<?php
class PHP_Email_Form {
    public $to;
    public $from_name;
    public $from_email;
    public $subject;
    public $ajax = false;
    public $smtp = array();
    private $messages = array();

    public function add_message($content, $label, $priority = 0) {
        $this->messages[] = [
            'content' => $content,
            'label' => $label,
            'priority' => $priority
        ];
    }

    public function send() {
        if (empty($this->to) || empty($this->from_email) || empty($this->subject)) {
            return json_encode(["status" => "error", "message" => "Invalid email configuration."]);
        }

        $email_content = "";
        foreach ($this->messages as $msg) {
            $email_content .= $msg['label'] . ": " . $msg['content'] . "\n";
        }

        $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (!empty($this->smtp)) {
            return $this->send_smtp_email($email_content, $headers);
        }

        if (mail($this->to, $this->subject, $email_content, $headers)) {
            return json_encode(["status" => "success", "message" => "Email sent successfully."]);
        } else {
            return json_encode(["status" => "error", "message" => "Failed to send email."]);
        }
    }

    private function send_smtp_email($email_content, $headers) {
        require 'PHPMailer/PHPMailer.php';
        require 'PHPMailer/SMTP.php';
        require 'PHPMailer/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = $this->smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtp['username'];
        $mail->Password = $this->smtp['password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $this->smtp['port'];

        $mail->setFrom($this->from_email, $this->from_name);
        $mail->addAddress($this->to);
        $mail->Subject = $this->subject;
        $mail->Body = $email_content;

        if ($mail->send()) {
            return json_encode(["status" => "success", "message" => "Email sent successfully."]);
        } else {
            return json_encode(["status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo]);
        }
    }
}
?>
