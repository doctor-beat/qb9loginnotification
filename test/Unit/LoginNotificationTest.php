<?php
defined('_JEXEC') or die;

require_once 'LoginNotification.php';
require_once 'classes/User.php';
require_once 'classes/Log.php';

use Joomla\CMS\Mail\Mail;
use Joomla\CMS\User\User;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;


class LoginNotificationTest extends MockeryTestCase {
    /**
     * @var LoginNotification
     */
    private $obj;


    /**
     *  @var MockInterface
     */
    private $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '1.1.1.1.';

        $this->mailer = m::spy(Mail::class);
        $this->mailer->shouldReceive('Send')->once()->andReturn(true);

        $this->obj = new LoginNotification($this->mailer);
    }


    public function testDefaultNotification() {
        $user = $this->buildUser("");
        $time = time();
        $this->obj->setTestTime($time);
        date_default_timezone_set('UTC');

        $this->obj->notify($user);

        $this->mailer->shouldHaveReceived()->addRecipient(m::capture($mailRecipient))->once();
        $this->mailer->shouldHaveReceived()->setSubject(m::capture($mailSubject))->once();
        $this->mailer->shouldHaveReceived()->setBody(m::capture($mailBody))->once();

        $expectedFormattedTime = DateTime::createFromFormat('U', time())
            ->setTimezone(new DateTimeZone("UTC"))
            ->format('r');
        assertThat($mailRecipient, is($user->email));
        assertThat($mailSubject, containsString("New Joomla-login for user '{$user->username}' on"));
        assertThat($mailSubject, containsString(" on '{$expectedFormattedTime}'"));
        assertThat($mailBody, containsString("ip: 1.1.1.1"));
    }

    public function testTimezoneBerlinViaPhp() {
        $user = $this->buildUser("");
        $time = time();
        $this->obj->setTestTime($time);
        date_default_timezone_set('Europe/Berlin');

        $this->obj->notify($user);

        $this->mailer->shouldHaveReceived()->addRecipient(m::capture($mailRecipient))->once();
        $this->mailer->shouldHaveReceived()->setSubject(m::capture($mailSubject))->once();
        $this->mailer->shouldHaveReceived()->setBody(m::capture($mailBody))->once();

        $expectedFormattedTime = DateTime::createFromFormat('U', time())
            ->setTimezone(new DateTimeZone("Europe/Berlin"))
            ->format('r');
        assertThat($mailRecipient, is($user->email));
        assertThat($mailSubject, containsString(" for user '{$user->username}' on"));
        assertThat($mailSubject, containsString(" on '{$expectedFormattedTime}'"));
        assertThat($mailBody, containsString("ip: 1.1.1.1"));
    }

    public function testTimezoneBerlinViaUser() {
        $user = $this->buildUser("Europe/Rome");
        $time = time();
        $this->obj->setTestTime($time);
        date_default_timezone_set('UTC');

        $this->obj->notify($user);

        $this->mailer->shouldHaveReceived()->addRecipient(m::capture($mailRecipient))->once();
        $this->mailer->shouldHaveReceived()->setSubject(m::capture($mailSubject))->once();
        $this->mailer->shouldHaveReceived()->setBody(m::capture($mailBody))->once();

        $expectedFormattedTime = DateTime::createFromFormat('U', time())
            ->setTimezone(new DateTimeZone("Europe/Berlin"))
            ->format('r');
        assertThat($mailRecipient, is($user->email));
        assertThat($mailSubject, containsString(" for user '{$user->username}' on"));
        assertThat($mailSubject, containsString(" on '{$expectedFormattedTime}'"));
        assertThat($mailBody, containsString("ip: 1.1.1.1"));
    }

    public function testNotificationToAdmins() {
        $user = $this->buildUser("");
        $time = time();
        $this->obj->setTestTime($time);
        date_default_timezone_set('UTC');

        $this->obj->notify($user, ['admin1@domain.com', 'admin2@domain.com']);

        $this->mailer->shouldHaveReceived()->addRecipient('admin1@domain.com')->once();
        $this->mailer->shouldHaveReceived()->addRecipient('admin2@domain.com')->once();
        $this->mailer->shouldNotHaveReceived()->addRecipient();
    }

    private function buildUser(string $timezone = ""): User {
        $user = new User();
        $user->email = 'test@x.com';
        $user->username = 'admin';

        $params = new stdClass();
        $params->timezone = $timezone;
        $params->admin_style = "";
        $params->admin_language = "";
        $params->language = "";
        $params->editor = "";

        $user->params = json_encode($params);
        return $user;
    }

}
