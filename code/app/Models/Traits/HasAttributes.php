<?php

namespace App\Models\Traits;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Arr;

trait HasAttributes
{
    // Usage: $equipment->setAttributeValue('warranty_years', 2);
    public function setAttributeValue(string $name, $value)
    {
        $entityType = $this->getTable(); // crude but works (e.g., 'equipment')
        $attr = Attribute::firstOrCreate([
            'name'        => $name,
            'entity_type' => $entityType,
        ], [
            'data_type'   => $this->guessDataType($value),
        ]);

        $payload = [
            'attribute_id' => $attr->id,
            'entity_id'    => $this->id,
            'value_string' => null,
            'value_text'   => null,
            'value_int'    => null,
            'value_bool'   => null,
            'value_date'   => null,
        ];

        switch ($attr->data_type) {
            case 'int':
                $payload['value_int'] = (int) $value; break;
            case 'bool':
                $payload['value_bool'] = (bool) $value; break;
            case 'date':
                $payload['value_date'] = $value; break;
            case 'text':
                $payload['value_text'] = (string) $value; break;
            default:
                $payload['value_string'] = (string) $value; break;
        }

        AttributeValue::updateOrCreate(
            ['attribute_id' => $attr->id, 'entity_id' => $this->id],
            $payload
        );
    }

    public function getAttributeValue(string $name)
    {
        $entityType = $this->getTable();
        $attr = Attribute::where(['name' => $name, 'entity_type' => $entityType])->first();
        if (!$attr) return null;
        $val = $attr->values()->where('entity_id', $this->id)->first();
        if (!$val) return null;

        foreach (['value_string','value_text','value_int','value_bool','value_date'] as $k) {
            if (!is_null($val->$k)) return $val->$k;
        }
        return null;
    }

    protected function guessDataType($value): string
    {
        if (is_int($value)) return 'int';
        if (is_bool($value)) return 'bool';
        if ($value instanceof \DateTimeInterface) return 'date';
        if (is_string($value) && strlen($value) > 255) return 'text';
        return 'string';
    }
}
