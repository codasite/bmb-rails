<?php

interface Wp_Bracket_Builder_Email_Service_Interface {

    public function ping_server();
    public function send_message($to_email, $to_name, $subject, $message, $html );
}
