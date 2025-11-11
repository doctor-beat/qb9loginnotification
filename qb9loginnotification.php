<?php
defined('_JEXEC') or die;

require_once 'LoginNotification.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;


class PlgUserQb9loginnotification extends CMSPlugin
{
    private $logid = 'qb9-loginnotification';

    public function onUserAfterLogin(array $options): void
    {
        try {
            Log::add('user.onUserAfterLogin for ' . $options['user']->email, Log::INFO, $this->logid);

            $user = $options['user'];
            if ((new LoginNotification(Factory::getMailer()))->notify($user, $this->getAdminEmails())) {
                Log::add('user.onUserAfterLogin; email sent.', Log::INFO, $this->logid);
            }
        } catch (Exception $e) {
            Log::add("PHP ERROR: " . $e->getMessage() . print_r($e, true), Log::ERROR, $this->logid);
        } catch (Error $e) {
            Log::add("PHP ERROR: " . $e->getMessage() . print_r($e, true), Log::ERROR, $this->logid);
        }
    }

    private function getAdminEmails() {
        include 'config.php';
        if (isset($CONFIG)) {
            if (isset($CONFIG->adminEmails)) {
                $emails = explode(',', $CONFIG->adminEmails);
                Log::add(print_r($emails, true), Log::INFO, $this->logid);

                return array_values(
                    array_filter($emails, function ($v) {
                        return str_contains($v, '@');
                    })
                );
            }
        }
        return [];
    }
}
