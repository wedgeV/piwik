<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Login
 */
namespace Piwik\Plugins\Login;

use Piwik\Piwik;
use Piwik\QuickForm2;

/**
 * Contains form related logic for the form that lets users set their password after
 * requesting to reset it.
 * 
 * @package Login
 */
class FormSetNewPassword extends QuickForm2
{
    public function __construct($id = 'setnewpasswordform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    public function init()
    {
        $password = $this->addElement('password', 'form_password');
        $password->addRule('required', Piwik::translate('General_Required', Piwik::translate('General_Password')));

        $passwordBis = $this->addElement('password', 'form_password_bis');
        $passwordBis->addRule('required', Piwik::translate('General_Required', Piwik::translate('Login_PasswordRepeat')));
        $passwordBis->addRule('eq', Piwik::translate('Login_PasswordsDoNotMatch'), $password);

        $this->addElement('checkbox', 'form_rememberme');

        $this->addElement('hidden', 'form_nonce');

        $this->addElement('submit', 'submit');
    }
}