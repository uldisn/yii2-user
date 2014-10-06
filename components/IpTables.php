<?php

class IpTables
{
    public static function validate($user_id)
    {
        
        if (empty($_SERVER['REMOTE_ADDR'])) {
            return false;
        }
        
        $long_ip = ip2long($_SERVER['REMOTE_ADDR']);
        
        if ($long_ip == -1 || $long_ip === false) {
            return false;
        }
        
        $long_ip = sprintf('%u', $long_ip);
        
        $criteria = new CDbCriteria();
        $criteria->compare('t.uxip_user_id', $user_id);
        $user_ip_list = UxipUserXIpTable::model()->findAll($criteria);
        
        foreach ($user_ip_list as $user_ip) {
            
            $ip = IptbIpTable::model()->findByPk($user_ip->uxip_iptb_id);
            
            if ($ip->iptb_status == BaseIptbIpTable::IPTB_STATUS_ACTIVE) {
                
                $long_from = ip2long($ip->iptb_from);
                $long_to   = ip2long($ip->iptb_to);
                
                if ($long_from == -1 || $long_from === false) {
                    continue;
                }
                
                if ($long_to == -1 || $long_to === false) {
                    continue;
                }
                
                $long_from = sprintf('%u', $long_from);
                $long_to   = sprintf('%u', $long_to);
                
                if ($long_ip >= $long_from && $long_ip <= $long_to) {
                    return true;
                }
                
            }
            
        }
        
        return false;
        
    }
}

