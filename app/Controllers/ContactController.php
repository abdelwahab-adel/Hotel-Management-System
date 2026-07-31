<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ContactMessage;
use App\Services\MailService;

final class ContactController extends Controller
{
    public function show(): string
    {
        return $this->view('home.contact');
    }

    public function submit(): string
    {
        $data = [
            'name'    => $this->input('name', ''),
            'email'   => $this->input('email', ''),
            'subject' => $this->input('subject', ''),
            'message' => $this->input('message', ''),
        ];

        $v = new Validator($data);
        $v->required('name', 'Name')->maxLength('name', 150, 'Name')
          ->required('email', 'Email')->email('email')
          ->required('subject', 'Subject')->maxLength('subject', 200, 'Subject')
          ->required('message', 'Message')->maxLength('message', 2000, 'Message');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Session::flash('_old', $data);
            return $this->redirect('/contact');
        }

        ContactMessage::create($data);

        // Best-effort email notification; the message is safely stored either way.
        (new MailService())->send(
            (string) setting('contact_notify_email', 'info@example.com'),
            'New contact message: ' . $data['subject'],
            "From: {$data['name']} <{$data['email']}>\n\n{$data['message']}"
        );

        Session::flash('success', 'Thanks for reaching out — we will get back to you shortly.');
        return $this->redirect('/contact');
    }
}
