<?php

interface Wp_Bracket_Builder_Email_Service_Interface {

    public function ping_server();
    public function send_message($from_email, $to_email, $to_name, $subject, $message, $html );
    public function create_template(string $name, string $from_email, string $from_name, string $subject, string $code, string $text, bool $publish, array $labels);
    public function get_or_create_template(string $name, string $from_email, string $from_name, string $subject, string $code, string $text, bool $publish, array $labels);
}

?>