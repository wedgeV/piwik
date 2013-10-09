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
 *
 * @package Login
 */
class FormResetPassword extends QuickForm2
{
    public function __construct($id = 'resetpasswordform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    public function init()
    {
        $this->addElement('text', 'form_login')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('General_Username')));

        $this->addElement('hidden', 'form_nonce');

        $this->addElement('submit', 'submit');
    }
}