<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(15);
        return Inertia::render('Admin/Contacts/Index', [
            'contacts' => $contacts
        ]);
    }

    public function updateStatus(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,read,replied'
        ]);

        $contact->update($validated);

        return redirect()->back()->with('flash', [
            'success' => 'Contact status updated successfully.'
        ]);
    }
}
