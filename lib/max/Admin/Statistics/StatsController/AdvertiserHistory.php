<?php

/*
+---------------------------------------------------------------------------+
| Openads v2.3                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/lib/max/Admin/Statistics/StatsHistoryController.php';



class StatsAdvertiserHistoryController extends StatsHistoryController
{
    function start()
    {
        // Get the preferences
        $pref = $GLOBALS['_MAX']['PREF'];

        // Get parameters
        if (phpAds_isUser(phpAds_Client)) {
            $advertiserId = phpAds_getUserId();
        } else {
            $advertiserId = (int)MAX_getValue('clientid', '');
        }


        // Security check
        phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);
        if (!MAX_checkAdvertiser($advertiserId)) {
            phpAds_PageHeader('2');
            phpAds_Die ($GLOBALS['strAccessDenied'], $GLOBALS['strNotAdmin']);
        }

        // Add standard page parameters
        $this->pageParams = array('clientid' => $advertiserId);
        $this->pageParams['period_preset']  = MAX_getStoredValue('period_preset', 'today');
        $this->pageParams['statsBreakdown'] = MAX_getStoredValue('statsBreakdown', 'day');

        $this->loadParams();

        // HTML Framework
        if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
            $this->pageId = '2.1.1';
            $this->pageSections = array('2.1.1', '2.1.2', '2.1.3');
        } elseif (phpAds_isUser(phpAds_Client)) {
            $this->pageId = '1.1';
            $this->pageSections = array('1.1', '1.2', '1.3');
        }

        $this->addBreadcrumbs('advertiser', $advertiserId);

        // Add context
        $this->pageContext = array('advertisers', $advertiserId);

        // Add shortcuts
        if (!phpAds_isUser(phpAds_Client)) {
            $this->addShortcut(
                $GLOBALS['strClientProperties'],
                'advertiser-edit.php?clientid='.$advertiserId,
                'images/icon-advertiser.gif'
            );
        }

        // Use the day span selector
        $this->initDaySpanSelector();

        $aParams = array();
        $aParams['advertiser_id'] = $advertiserId;

        $this->prepareHistory($aParams, 'stats.php?entity=advertiser&breakdown=daily');



        $period_preset  = MAX_getStoredValue('period_preset', 'today');
        $statsBreakdown = MAX_getStoredValue('statsBreakdown', 'day');

        // Add module page parameters
        $this->pageParams = array('clientid' => $advertiserId,
                                  'entity' => $entity, 'breakdown' => $breakdown,
                                  'period_preset' => $period_preset,
                                  'statsBreakdown' => $statsBreakdown
                                 );

        $this->loadParams($this->pageParams);

    }
}

?>
