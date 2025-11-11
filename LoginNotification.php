<?php
defined('_JEXEC') or die;

use Joomla\CMS\User\User;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\Exception\MailDisabledException;


class LoginNotification {
    /**
     * @var Mail
     */
    private $mailer;
    private $logid = 'qb9-loginnotification';
    private $testTime = null;

    /**
     * @param Mail $mailer
     */
    public function __construct(Mail $mailer)
    {
        $this->mailer = $mailer;
        $this->testTime = null;
    }

    /**
     * Use for testing only
     * @param int $time
     * @return void
     */
    public function setTestTime(int $time) {
        $this->testTime = $time;
    }


    public function notify(User $user, array $adminEmails = []): bool
    {
        $params = json_decode($user->params);
        $timezone = (isset($params->timezone) && strlen($params->timezone) > 0) ? $params->timezone : date_default_timezone_get();
        $timestamp = DateTime::createFromFormat('U', $this->getTime())
            ->setTimezone(new DateTimeZone($timezone))
            ->format('r');
        $ip = $this->getIPAddr();

        $subject = "New Joomla-login for user '{$user->username}' on '$timestamp'";
        $body = "New Joomla-login\n"
            .   " - user: {$user->username}\n"
            .   " - email: {$user->email}\n"
            .   " - time: {$timestamp}\n"
            .   " - ip: {$ip}\n"
            .   "\nIf this was you then please ignore this email.\n";
        $recipients = $adminEmails;
        if (!isset($recipients) || count($recipients) == 0) {
            $recipients = [$user->email];
        }

        return $this->sendMail($subject, $body, $recipients);
    }

    function sendMail(string $subject, string $body, array $emailAddresses): bool
    {
        $mail = $this->mailer;
        $mail->setSubject($subject);
        $mail->setBody($body);
        foreach ($emailAddresses as $recipient)
        {
            $mail->addRecipient($recipient);
        }

        $sendResult = false;
        $errorMsg = '';
        try {
            $sendResult = $mail->Send();
        }
        catch (phpmailerException $e)
        {
            $errorMsg .= $e->errorMessage();
        }
        catch (MailDisabledException $e)
        {
            $errorMsg .= $e->getReason();
        }
        catch (Exception $e)
        {
            $errorMsg .= $e->getMessage();
        }

        if (!$sendResult) {
            Log::add('FAILED: Sent email to ' . implode(", ", $emailAddresses) .
                ', subject: ' . $subject . '; not successful: ' . $errorMsg
                , Log::ERROR, $this->logid);
        }
        return $sendResult;
    }

    private function getIPAddr(): string {
        // source: http://stackoverflow.com/a/2031935
        $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($keys as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        Log::add('No proper remote IP address available, falling back to REMOTE_ADDR "'.$_SERVER['REMOTE_ADDR']. '"!', Log::WARNING, $this->logid);
        return $_SERVER['REMOTE_ADDR'];
    }

    private function getTime(): int {
        return $this->testTime ?? time();
    }


}
