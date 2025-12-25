<?php
/**
 * Strict / Pure EAV helpers.
 *
 * Storage tables: entities, eav_attributes, eav_values
 * Compatibility: views users/courses/sections/enrollments/grades exist for READs.
 *
 * NOTE: There are NO helper/index tables. Uniqueness and constraints are enforced
 * in code using SELECT checks (as required by a pure EAV schema).
 */

require_once 'db.php';

/** @return PDO */
function eav_db(): PDO {
    return getDB();
}

/**
 * Fetch attribute metadata for (entity_type, name).
 * @return array{ id:int, data_type:string }
 */
function eav_attr(string $entity_type, string $name): array {
    static $cache = [];
    $key = $entity_type . '::' . $name;
    if (isset($cache[$key])) return $cache[$key];

    $pdo = eav_db();
    $stmt = $pdo->prepare("SELECT id, data_type FROM eav_attributes WHERE entity_type = ? AND name = ?");
    $stmt->execute([$entity_type, $name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new RuntimeException("EAV attribute not found: {$entity_type}.{$name}");
    }
    $cache[$key] = ['id' => (int)$row['id'], 'data_type' => $row['data_type']];
    return $cache[$key];
}

/** Create an entity row and return its id. */
function eav_create_entity(string $entity_type): int {
    $pdo = eav_db();
    $stmt = $pdo->prepare("INSERT INTO entities (entity_type) VALUES (?)");
    $stmt->execute([$entity_type]);
    return (int)$pdo->lastInsertId();
}

/**
 * Set an attribute value (upsert) for an entity.
 * Handles typed storage columns.
 */
function eav_set(int $entity_id, string $entity_type, string $attr_name, $value): void {
    $pdo = eav_db();
    $attr = eav_attr($entity_type, $attr_name);
    $aid = $attr['id'];
    $type = $attr['data_type'];

    // Delete existing value row for (entity_id, attribute_id)
    $del = $pdo->prepare("DELETE FROM eav_values WHERE entity_id = ? AND attribute_id = ?");
    $del->execute([$entity_id, $aid]);

    // Insert new row
    $cols = [
        'string' => 'value_string',
        'text'   => 'value_text',
        'int'    => 'value_int',
        'bool'   => 'value_bool',
    ];
    if (!isset($cols[$type])) throw new RuntimeException("Unsupported EAV type: {$type}");
    $col = $cols[$type];

    $sql = "INSERT INTO eav_values (entity_id, attribute_id, {$col}) VALUES (?,?,?)";
    $ins = $pdo->prepare($sql);

    // Normalize bool/int
    if ($type === 'bool') {
        $value = $value ? 1 : 0;
    }
    if ($type === 'int' && $value !== null && $value !== '') {
        $value = (int)$value;
    }
    $ins->execute([$entity_id, $aid, $value]);
}

/** Get a scalar attribute value for an entity (returns null if missing). */
function eav_get(int $entity_id, string $entity_type, string $attr_name) {
    $pdo = eav_db();
    $attr = eav_attr($entity_type, $attr_name);
    $aid = $attr['id'];
    $type = $attr['data_type'];

    $stmt = $pdo->prepare("SELECT value_string, value_text, value_int, value_bool FROM eav_values WHERE entity_id=? AND attribute_id=?");
    $stmt->execute([$entity_id, $aid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;

    return match ($type) {
        'string' => $row['value_string'],
        'text'   => $row['value_text'],
        'int'    => $row['value_int'] !== null ? (int)$row['value_int'] : null,
        'bool'   => $row['value_bool'] !== null ? (int)$row['value_bool'] : null,
        default  => null,
    };
}

/** Delete an entity and all its EAV rows (FK cascade handles eav_values). */
function eav_delete_entity(int $entity_id): void {
    $pdo = eav_db();
    $stmt = $pdo->prepare("DELETE FROM entities WHERE id = ?");
    $stmt->execute([$entity_id]);
}

// ---------------------------------------------------------------------
// Constraint checks (pure EAV, no redundancy)
// ---------------------------------------------------------------------

function eav_user_email_exists(string $email, ?int $exclude_user_id = null): bool {
    $pdo = eav_db();
    $sql = "
        SELECT COUNT(*) AS c
        FROM entities e
        JOIN eav_values v ON v.entity_id = e.id
        JOIN eav_attributes a ON a.id = v.attribute_id
        WHERE e.entity_type = 'user'
          AND a.entity_type = 'user'
          AND a.name = 'email'
          AND v.value_string = ?
    ";
    $params = [$email];
    if ($exclude_user_id !== null) {
        $sql .= " AND e.id <> ?";
        $params[] = $exclude_user_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return ((int)$stmt->fetchColumn()) > 0;
}

function eav_course_code_exists(string $code, ?int $exclude_course_id = null): bool {
    $pdo = eav_db();
    $sql = "
        SELECT COUNT(*) AS c
        FROM entities e
        JOIN eav_values v ON v.entity_id = e.id
        JOIN eav_attributes a ON a.id = v.attribute_id
        WHERE e.entity_type = 'course'
          AND a.entity_type = 'course'
          AND a.name = 'code'
          AND v.value_string = ?
    ";
    $params = [$code];
    if ($exclude_course_id !== null) {
        $sql .= " AND e.id <> ?";
        $params[] = $exclude_course_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return ((int)$stmt->fetchColumn()) > 0;
}

/**
 * Find enrollment entity id for a student+course (returns null if not found).
 */
function eav_find_enrollment_id(int $student_id, int $course_id): ?int {
    $pdo = eav_db();
    $sql = "
        SELECT e.id
        FROM entities e
        JOIN eav_values v1 ON v1.entity_id = e.id
        JOIN eav_attributes a1 ON a1.id = v1.attribute_id
        JOIN eav_values v2 ON v2.entity_id = e.id
        JOIN eav_attributes a2 ON a2.id = v2.attribute_id
        WHERE e.entity_type = 'enrollment'
          AND a1.entity_type = 'enrollment' AND a1.name = 'student_id' AND v1.value_int = ?
          AND a2.entity_type = 'enrollment' AND a2.name = 'course_id' AND v2.value_int = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $course_id]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int)$id : null;
}

/**
 * Find enrollment entity id for a student+section (returns null if not found).
 */
function eav_find_enrollment_id_by_section(int $student_id, int $section_id): ?int {
    $pdo = eav_db();
    $sql = "
        SELECT e.id
        FROM entities e
        JOIN eav_values v1 ON v1.entity_id = e.id
        JOIN eav_attributes a1 ON a1.id = v1.attribute_id
        JOIN eav_values v2 ON v2.entity_id = e.id
        JOIN eav_attributes a2 ON a2.id = v2.attribute_id
        WHERE e.entity_type = 'enrollment'
          AND a1.entity_type = 'enrollment' AND a1.name = 'student_id' AND v1.value_int = ?
          AND a2.entity_type = 'enrollment' AND a2.name = 'section_id' AND v2.value_int = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $section_id]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int)$id : null;
}

/** Count enrollments for a given section (used for capacity logic). */
function eav_count_enrollments_for_section(int $section_id): int {
    $pdo = eav_db();
    $sql = "
        SELECT COUNT(*)
        FROM entities e
        JOIN eav_values v ON v.entity_id = e.id
        JOIN eav_attributes a ON a.id = v.attribute_id
        WHERE e.entity_type = 'enrollment'
          AND a.entity_type = 'enrollment'
          AND a.name = 'section_id'
          AND v.value_int = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$section_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Check if a (course_id, section_number) already exists.
 */
function eav_section_exists(int $course_id, int $section_number): bool {
    $pdo = eav_db();
    $sql = "
        SELECT COUNT(*)
        FROM entities e
        JOIN eav_values v1 ON v1.entity_id = e.id
        JOIN eav_attributes a1 ON a1.id = v1.attribute_id
        JOIN eav_values v2 ON v2.entity_id = e.id
        JOIN eav_attributes a2 ON a2.id = v2.attribute_id
        WHERE e.entity_type = 'section'
          AND a1.entity_type='section' AND a1.name='course_id' AND v1.value_int = ?
          AND a2.entity_type='section' AND a2.name='section_number' AND v2.value_int = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id, $section_number]);
    return ((int)$stmt->fetchColumn()) > 0;
}

// ---------------------------------------------------------------------
// Cascading deletions (because EAV attributes cannot FK-cascade)
// ---------------------------------------------------------------------

/** Delete all enrollments for a given course. */
function eav_delete_enrollments_by_course(int $course_id): void {
    $pdo = eav_db();
    $sql = "
        SELECT e.id
        FROM entities e
        JOIN eav_values v ON v.entity_id=e.id
        JOIN eav_attributes a ON a.id=v.attribute_id
        WHERE e.entity_type='enrollment'
          AND a.entity_type='enrollment' AND a.name='course_id' AND v.value_int=?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) {
        eav_delete_entity((int)$id);
    }
}

/** Delete all enrollments for a given section. */
function eav_delete_enrollments_by_section(int $section_id): void {
    $pdo = eav_db();
    $sql = "
        SELECT e.id
        FROM entities e
        JOIN eav_values v ON v.entity_id=e.id
        JOIN eav_attributes a ON a.id=v.attribute_id
        WHERE e.entity_type='enrollment'
          AND a.entity_type='enrollment' AND a.name='section_id' AND v.value_int=?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$section_id]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) {
        eav_delete_entity((int)$id);
    }
}

/** Delete all sections for a given course (and their enrollments). */
function eav_delete_sections_by_course(int $course_id): void {
    $pdo = eav_db();
    $sql = "
        SELECT e.id
        FROM entities e
        JOIN eav_values v ON v.entity_id=e.id
        JOIN eav_attributes a ON a.id=v.attribute_id
        WHERE e.entity_type='section'
          AND a.entity_type='section' AND a.name='course_id' AND v.value_int=?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id]);
    $section_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($section_ids as $sid) {
        $sid = (int)$sid;
        eav_delete_enrollments_by_section($sid);
        eav_delete_entity($sid);
    }
}
