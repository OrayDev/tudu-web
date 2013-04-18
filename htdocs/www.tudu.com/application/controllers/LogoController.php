<?php
/**
 * Logo Controller
 *
 * @author Hiro
 * @version $Id: LogoController.php 2182 2012-09-29 06:22:43Z cutecube $
 */

class LogoController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $bootstrap = $this->getInvokeArg('bootstrap');
        $multidb = $bootstrap->getResource('multidb');
        $options = $bootstrap->getOptions();

        $orgId = $this->_request->getQuery('oid');
        $uniqueId = $this->_request->getQuery('unid');
        $email = $this->_request->getQuery('email');

        if (array_key_exists('unid', $_GET) || array_key_exists('email', $_GET)) {

            if (!empty($uniqueId) || !empty($email)) {

                /* @var $userDao Dao_Md_User_User */
                $userDao = Oray_Dao::factory('Dao_Md_User_User', $multidb->getDb());

                if ($uniqueId) {
                   $avatars = $userDao->getAvatars($uniqueId);
                } else {
                   $arr = explode('@', $email, 2);
                   $userId = $arr[0];
                   $orgId  = $arr[1];
                   $avatars = $userDao->getAvatars(array('userid' => $userId, 'orgid' => $orgId));
                }
                if (!empty($avatars['avatars']) && !empty($avatars['type'])) {
                    $type = $avatars['type'];
                    $logo = $avatars['avatars'];
                }
            }

            if (!isset($logo)) {
                $type = 'image/png';
                if (!isset($avatars['gender']) || $avatars['gender']) {
                    $logo = base64_decode('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');
                } else {
                    $logo = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAIAAAABc2X6AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAjWSURBVHja7FtpbxvXFT13hqQ2W94Vy3Aco6kbpGkKFO2v77d+SYsiQJA2Lpw0Xhovsi1rISmKHJLzTj/MW+57M5TrRnJGCQeCqOEM7Tnv3PW8S3n06BF+SUeGX9ixBLwEvAS8BLwEvATcoqPzHv4PKcve8Lg7GObjIpvNTZ4zz8rVFdPtlGsrs80LzLKfA+BsOlvpH/UGo+7wCABJACAyzAF2h8cAADLLZpsXiquXplc2zy1gYuP5q9U3hwADVH/N/bJXStPd73f3++VKb/Sr2/ML6+fPhzeev1rdO/SwFFQmf4sAIAQA8qLYfPBodWf3nDG8une4uncYMyqB5hrZ2tlhuP50h3lWbF07H4DFmLWdN95dlQ3TgwKpL7mF8Ohl/ckL0+vOLm+eA5Pu7Q9kPg/YwNiQGaG1l9Qd9gaz9sPO+fDhlf3DyEmZIg0/Cd7YwfPjSWc4ajvgvJjmo4nDVr1W2BjFZ3vFhmkHngTtARDovjloO+DeXj8iKzgngwEzJh1Nfk6C7O4PQLYacLc/iAw3mKmgxjl9yPI/inQSUhRnYdWnBjgrptlxoYyZ1GbaZM/VH2GJnD37aNfR6a1tgHv7A5g4NHkr9UbtPdYvQwhf9oM0pLEr1HnTYsCdw0FwxcRKExcNHq4CNONahASZFdNsUrQRsMzL/OhYGXBspVH21emoOhMVpZFEr87BoI2A86MRjAGo7JNI6i27Ht66vT0zyVh6sbL+sI2lZefwiNQGadsCRZeCFGppMj5Vpm5P8+G4jYCzo2OCIAQCECIBvOjETHfqr0rs7VDxHABkPAYZlq8lgGU+hyFEKOpx6yTXXDTqltOWgwBokI0Ls77aLsAmy4WOwsAG620g41YJ6WlDHSqTAm0DLGVZceoYq4xQ4pawoUMk0US+v0oAMi5a58P0JYVUbuyeXmIZQJdcaTntCk4dwyo3LqatFADsw4qLTBXhb4vVImxMYMrGWwl4NmeF8C0kQ8uXUQXmVoG22VDkz+YtNGkjHlsVeCSgTg1hUSrWru7TGIHStI9hXzq5WCWIThO0rNMb9C1JO6p52xg2BsW0MmmBlV0t3Wy2apWE0OTbWrYG86xdgLP+EQxDnK1YJVV1lIauqEmo1yFxrmK32y7AMhzREGIJlmCyJ5WZMbm6AknlW3OqWzCnYC0yGFmvM8Y3hNRdsWHUJCsFgI3vA/r97PV+y3x4Utic5PxSRACh9t7EqlMtnrHih1BpkRiN2wWYnQ49Tir13TtzvS5rSkKq4Izdu9dtl0nzxhUYgqABTejxQ0NfKVVa+QkiiMAbuEEk5XqH2NxoF8PmxpUsy2CMagcJEdck1pvZWjQWaGk+aTN4a6tlEk+vW/7xU2RCY6qYY65dRqdTxTArROojcVpDWP6p62rL+vYNXrpwioDl1GYti2n2w0s5GGBemk/uys5u9vy1JddX15ENpG0Tb22ZOzd5+SImUxlPMJtzYw0ba22UeABgpWfu3QlWe+kCNzcky7L7D20L1dBaRaJH+dnHqIqq1R5Xezib48ymSTo572ybm9fptPV4l1CgIpkWbs/6OOPxmeksisuMShC3peJIPtW+9ycCPC99JGNzvQVfV8nh8D0APrOxpWIq3z/FXp9JdR0017gNFMF/dvDBdeRny4GcyUT8eIIv/iHHE5tLHUL/Gkcs1Ruvr/Lze7hx5VwBPhjg7/dRFJbGqoXysCUuRELiVT3j7S18fg/dznkw6Scv+PV33m7FyVremJUSEpNMVU0/fc3dQ/n9PWxfbzHDuwfmn//GwRAi4uqNwKpqlheSXBcGrl6Su7dwewunN4x5GoAHI/PN93zxWhQ8OHji4XlHFmnyZKbypZd7ej25e0vubOPi+k8MmLsH5tsnfP66zqoPUFCBKgKfkhyLmKm4R5By+aJ8eFPubGNt5X0DNk9fmgePudd3MBCHqBp4De+tJMclp9qXsS+ydUU+vJnd/gArvbMFzKNj8/CZefSMx5OA0z296xAS1w23RS3ECSTHCq6CnUjWlO3r+W8+ku0bpw2YNE9elA+fmZ1diN8elJgu6IAU9hCl+ba3enJs25Fwm0olVzc7f/qtXLt8OoDNq/3ZX79mfxjHJJVgXZ6tG3MDzkUkM+oWF5Jct/Aqz2VZ/odP808++rF52OweTP/8Bcqy2vUSr7lahV2Cdge9T+9KSNJ+in42OtarfU4Wuy9VJWWJk7MkU+b6cvV+acov72M2y3/36x8B2JjpX77kbG4LhrBDKIAFz2h7BVqpFHdz2BcnRYRqOSL1hw27LWQ0AeEKUW3SYf9x/tUDiOSfffx/dkvzfz1mf+TmxdTAEQLHNnEY6v626n718FIUm0wYbIr0PYg20nCKsHCqcRbqYUZUjMjsqwfmxJn6EwF/9zhqXyNg6m37/GEiCQhNX1DnTQzcagJNIdK3ynDAonimtAL3DQKCrCAbzv72zQlTqQsBczgye9U0IezWQaxZRAHTbi8onbVS8CJzrR7b6Jttk2zlPhWTWBvGBVGz/2S00a7F4MjsvHlnwOXLvYReJtqiUiET3rzhab0yGEiYvDSoLSQT3hDMVU1r+lzs5hbVl0UgYhaPsy0MWmavT0OfTqlCj1RzClVcDbEqhC7bIUkIxSJKxyPFTUNUppIK14y/EaJE3Wo7LpoOaopeHE/fmWG7SI4WmBoRrH9Ph5HYngh3Bk2Dtmz4bJTeXUXuIxPjU/s7+iQXj6QuZJjHEzDaFxI3wkGddeDYRsx22g+rfyHeZ3MDMJrlelpSAU5c8qtvuPqQNpm+u0lPZ24SySVh1ExUPXRVWqhdseqe+CPVg4pePlEz87p0oVscCY6cVloRzigHjCfvnpaKWWqqjbGefPvp/3JPWvLKiaeIynXUTuflu5t07KNUG7zxlr4OWvRVM1xgEz2w4QMb0WzVkfqzqEMW9WzOtrU5kCf48GKGs6w5Sp3ENuKsaEu0OHTF5Qbr38asky0pyUnEqqU1TmbvV6Zt8bH8SvwS8BLwEvAScJuP/w4A1g0Q+Xl8GmgAAAAASUVORK5CYII=');
                }
            }

            echo $logo;

        // 联系人头像
        } elseif (array_key_exists('ctid', $_GET) && array_key_exists('tsid', $_GET)) {

            $contactId = $_GET['ctid'];
            $tsId      = $_GET['tsid'];

            /* @var $daoContact Dao_Td_Contact_Contact */
            $daoContact = Oray_Dao::factory('Dao_Td_Contact_Contact', $multidb->getDb('ts' . $tsId));

            $info = $daoContact->getAvatars($contactId);

            if (null !== $info) {
                $logo = $info['avatars'];
                $type = $info['avatarstype'];
            }

            if (empty($logo)) {
                $type = 'image/png';

                $logo = base64_decode('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');
            }

            echo $logo;

        // 组织Logo
        } else {

            /* @var $orgDao Dao_Md_Org_Org */
            $orgDao = Oray_Dao::factory('Dao_Md_Org_Org', $multidb->getDb());
            $logo = $orgDao->getLogo($orgId);

            if (!$logo) {
                $logo = base64_decode('R0lGODlhqgAyAMQAAAAAAP///8jY10mAe4+xrtbi4R9jXC1tZjt3cFeKhWWUj3OemYGopJ27uLrPzazFwuTs6/L29f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABIALAAAAACqADIAAAX/YCCOZGmeaKqubOu+cCzPdG3feK7vfO//wKBwSCwaj8ikcslsOp/QqHRKrVqv2KwWy1AUtmBS5EEom8/odAMigxgMiSwjkfiCIYe3fs/v7wknEQKDhIV6hYiEbEsJbwJhC36Sk2+LIw55lJp6BxGMjmEIbw+eMQUDbw0loputnZ8Gj2BvcSYFiYSlAQJvgCNuBgMOg5EDuISRsYWWSY2xYbQlBZmTvgG9JLwGDiMEcCreBlLOslvRv9STtdcG1rupyMHHxI6FuiIQZkPk0N8jqHDSEMhkDdsIba02vTqoZx+oWf4CJDOAwM6IB28Q6DIoAmFCTcwQOnwGsVayA3kO/3ALEOGkRXbutC0w40wgzV5nynVsKIRfyQAYDRwoMO0NAwcAh5bg+I5kgHDg3qCAMKjBIUJfqAq4l22QCQFz6BBgw48QCkECmCWJZlVpgKJ7BqiFme3h0zcrbbFCEU5SnHA6SfAUEUFBnwMP+A0uoc3dWn8FdCXmM6DBS7oMnfbVtK7E5j5/7S6VShign0yPFtdt1+QcSwcMWNFKByeBF6bayuFRGPhX1auDvgBGMXiigkUP0qUm/ZXp4wQtZWccy5KA9F64RUdwYLOMA64nRHoWLZg0MAMLpFFbLi68cyS0HPBRUCZwAQIKRCEAFhNUvu42XbZaeyQMdwJP4SxEQv9Qz6iWmWPwwQGBAgksQEoAziBAHXGsZfYIbQk9gIJ4BZI3Ak/OpHcgKA7u1OESrpFg2B4KiGhCdg1+9MeIDhp4I2l6qHKCYswx9t4RMUawiAMTvaHSaO5Y9YwzwsxDj4kuEtgNluy0dwgKybDH44tKuBZBHpV5EkEDpikAZYkGeALXAAJepAcDKZC4pVOjecnlcC02BSGSEZ33Ro0iFLBAHW+OwABzBchGAFcRPPqGimNqKYKPfYrw5QmAFrnaoEbE6IAznCzQm6dkojKAGDNSZCNQ1AiZZ49c8pSJrSUQqemDrUX0SwPXaagWRxEc+pVsCDSAakUs6LnpkewcIEL/iiigFkAmfFFbRIzSMECbThzJR+aC1xmgAHjuiQqUUScAU4uUBsxl7pSVnGDprGUGwwKTesza2AhhxktsHwgcxe6AtmQE6qWEZdIZS7I9kgy/I7BSpxGsbKjCGLa2xFFKI9xCwALXDWCaHgkcVSdCDJSyCCsL3CNAJnn1ZYwIDkj3SFAHBJYMAk40qWMfdhRw6YSSJJx0bKe52ecetdx7wEwMmDax0XE9ZNoAZaCsR15LeHQ01Zk9QK+TCliGQgHO9mGCpWhPK8kA4H02ioERrMwHxktA0ACAhBPQwD2Gs8TATA9s/DYZLZ+QmCgDJMCvAGKzDPiwdLTMho9rrnz1IaphlG766ainrvrqrLfu+uuwxy777LTXbvvtuOeuu+khAAA7');
            }

            //$fp = fopen('php://output', 'r+', false);
            //fwrite($fp, $logo);
            //$info = getimagesize($fp);
            //$type = $info['mime'];
            $type = $this->_getFileType($logo);

            echo $logo;
        }

        $this->_response->setHeader('Expires', gmdate('D, d M Y H:i:s T', time() + 36000), true);
        $this->_response->setHeader('Cache-Control', 'private', true);
        $this->_response->setHeader('Pragma', 'private', true);
        $this->_response->setHeader('Content-type', $type);
        $this->_response->setHeader('Content-Length', strlen($logo));
    }

    private function _getFileType($bin)
    {
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';
        switch ($typeCode) {
            case 255216:
                $fileType = 'image/jpeg';
                break;
            case 7173:
                $fileType = 'image/gif';
                break;
            case 6677:
                $fileType = 'image/bmp';
                break;
            case 13780:
                $fileType = 'image/png';
                break;
            default:
                break;
        }

        return $fileType;
    }


}

