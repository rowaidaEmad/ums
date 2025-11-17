<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with(['sender','recipient'])
            ->where('recipient_id', Auth::id())
            ->latest()->paginate(10);
        return view('messages.index', compact('messages'));
    }

    public function create()
    {
        $users = User::where('id', '<>', Auth::id())->get();
        return view('messages.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required',
            'body' => 'required'
        ]);
        $data['sender_id'] = Auth::id();
        Message::create($data);
        return redirect()->route('messages.index')->with('success','Message sent.');
    }

    public function edit(Message $message)
    {
        $users = User::where('id', '<>', Auth::id())->get();
        return view('messages.edit', compact('message','users'));
    }

    public function update(Request $request, Message $message)
    {
        $data = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required',
            'body' => 'required'
        ]);
        $message->update($data);
        return redirect()->route('messages.index')->with('success','Message updated.');
    }

    public function destroy(Message $message)
    {
        $message->delete();
        return back()->with('success','Message deleted.');
    }
}
