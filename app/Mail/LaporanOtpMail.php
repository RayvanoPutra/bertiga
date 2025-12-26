<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LaporanOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public $nama;

    // Terima data OTP saat class dipanggil
    public function __construct($otp, $nama)
    {
        $this->otp = $otp;
        $this->nama = $nama;
    }

    public function build()
    {
        return $this->subject('Kode Keamanan Laporan PDF Anda')
            ->view('emails.otp_laporan');
    }
}
