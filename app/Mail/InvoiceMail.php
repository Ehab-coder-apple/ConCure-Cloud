<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $attachPdf;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, bool $attachPdf = true, string $customMessage = null)
    {
        $this->invoice = $invoice;
        $this->attachPdf = $attachPdf;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromEmail = $this->invoice->clinic->email ?? config('mail.from.address');
        $fromName = $this->invoice->clinic->name ?? config('mail.from.name');

        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} from {$this->invoice->clinic->name}",
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName)
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate URLs for the email
        $viewUrl = route('finance.invoices.public.view', [
            'invoice' => $this->invoice->id,
            'token' => $this->generateInvoiceToken()
        ]);
        
        $downloadUrl = route('finance.invoices.public.pdf', [
            'invoice' => $this->invoice->id,
            'token' => $this->generateInvoiceToken()
        ]);

        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'customMessage' => $this->customMessage,
                'viewUrl' => $viewUrl,
                'downloadUrl' => $downloadUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->attachPdf) {
            // Generate PDF and attach it
            $pdf = Pdf::loadView('finance.invoice-pdf', ['invoice' => $this->invoice]);
            
            $attachments[] = Attachment::fromData(
                fn () => $pdf->output(),
                "invoice-{$this->invoice->invoice_number}.pdf"
            )->withMime('application/pdf');
        }

        return $attachments;
    }

    /**
     * Generate a secure token for public invoice access.
     */
    private function generateInvoiceToken(): string
    {
        return hash_hmac('sha256', 
            $this->invoice->id . $this->invoice->invoice_number . $this->invoice->created_at,
            config('app.key')
        );
    }

    /**
     * Build the message (for backward compatibility).
     */
    public function build()
    {
        $mail = $this->subject("Invoice {$this->invoice->invoice_number} from {$this->invoice->clinic->name}")
                     ->from(
                         $this->invoice->clinic->email ?? config('mail.from.address'),
                         $this->invoice->clinic->name ?? config('mail.from.name')
                     )
                     ->replyTo(
                         $this->invoice->clinic->email ?? config('mail.from.address'),
                         $this->invoice->clinic->name ?? config('mail.from.name')
                     );

        // Generate URLs for the email
        $viewUrl = route('finance.invoices.public.view', [
            'invoice' => $this->invoice->id,
            'token' => $this->generateInvoiceToken()
        ]);
        
        $downloadUrl = route('finance.invoices.public.pdf', [
            'invoice' => $this->invoice->id,
            'token' => $this->generateInvoiceToken()
        ]);

        $mail->view('emails.invoice')
             ->with([
                 'invoice' => $this->invoice,
                 'customMessage' => $this->customMessage,
                 'viewUrl' => $viewUrl,
                 'downloadUrl' => $downloadUrl,
             ]);

        // Attach PDF if requested
        if ($this->attachPdf) {
            $pdf = Pdf::loadView('finance.invoice-pdf', ['invoice' => $this->invoice]);
            $mail->attachData(
                $pdf->output(),
                "invoice-{$this->invoice->invoice_number}.pdf",
                [
                    'mime' => 'application/pdf',
                ]
            );
        }

        return $mail;
    }
}
