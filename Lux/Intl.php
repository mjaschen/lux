<?php
/**
 * 
 * Class for i18n
 * 
 * @category Lux
 * 
 * @package Lux_Intl
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */

/**
 * 
 * Class for i18n
 * 
 * @category Lux
 * 
 * @package Lux_Intl
 * 
 */
class Lux_Intl extends Solar_Base {
    
    /**
     * 
     * Gets a country list
     * 
     * Gets the whole list of countries specified
     * in ISO 3166.
     * 
     * @return array An assoc array where key is
     * the country code and the value the country
     * name
     * 
     */
    public function getCountryList()
    {
        $list = $this->_getList('country');
        asort($list);
        return $list;
    }
    
    /**
     * 
     * Gets names of the weekdays
     * 
     * Gets names of the weekdays for current locale
     * 
     * @return array An assoc array where key is
     * the days short name and the value the actual
     * real name of the day
     * 
     */
    public function getWeekdays()
    {
        return $this->_getList('day');
    }
    
    /**
     * 
     * Gets names of the weekdays
     * 
     * Gets names of the weekdays for current locale
     *
     * @return array An assoc array where key is
     * the days short name and the value the actual
     * real name of the day
     * 
     */
    public function getMonths()
    {
        return $this->_getList('month');
    }
    
    /**
     * 
     * Gets a set of locale strings
     * 
     * Returns a list of locale strings from the locale
     * file
     * 
     * @return array
     * 
     */
    protected function _getList($key)
    {
        $property = "_$key";
        
        // count only once
        $count = count((array) $this->$property);
        $key = strtoupper($key);
        
        $list = array();
        for ($i=0; $i < $count; $i++) { 
            $list[$this->$property[$i]] = $this->locale(
                $key . '_' . $this->$property[$i]
            );
        }
        
        return $list;
    }
    
    /**
     * 
     * List of weekday 'keys'
     * 
     * @var array
     * 
     */
    private $_day = array('MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN');
    
    /**
     * 
     * List of month 'keys'
     * 
     * @var string
     * 
     */
    private $_month = array(
        'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
        'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC',
    );
    
    /**
     * 
     * List of valid country codes
     * 
     * @var array
     * 
     */
    private $_country = array(
        'AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT',
        'AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BV',
        'BR','IO','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN',
        'CX','CC','CO','KM','CG','CD','CK','CR','CI','HR','CU','CY','CZ','DK','DJ',
        'DM','DO','EC','EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF',
        'PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG',
        'GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE',
        'IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KP','KR','KW','KG','LA',
        'LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML',
        'MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ',
        'MM','NA','NR','NP','NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO',
        'OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE',
        'RO','RU','RW','SH','KN','LC','PM','VC','WS','SM','ST','SA','SN','RS','SC',
        'SL','SG','SK','SI','SB','SO','ZA','GS','ES','LK','SD','SR','SJ','SZ','SE',
        'CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC',
        'TV','UG','UA','AE','GB','US','UM','UY','UZ','VU','VE','VN','VG','VI','WF',
        'EH','YE','ZM','ZW',
    );
}