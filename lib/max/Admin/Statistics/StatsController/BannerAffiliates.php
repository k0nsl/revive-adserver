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

require_once MAX_PATH . '/lib/max/Admin/Statistics/StatsCrossEntityController.php';



class StatsBannerAffiliatesController extends StatsCrossEntityController
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
        $placementId  = (int)MAX_getValue('campaignid', '');
        $adId         = (int)MAX_getValue('bannerid', '');

        // Security check
        phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);
        if (!MAX_checkAd($advertiserId, $placementId, $adId)) {
            phpAds_PageHeader('2');
            phpAds_Die ($GLOBALS['strAccessDenied'], $GLOBALS['strNotAdmin']);
        }

        // Add standard page parameters
        $this->pageParams = array('clientid' => $advertiserId, 'campaignid' => $placementId, 'bannerid' => $adId);

        $this->pageParams['period_preset']  = MAX_getStoredValue('period_preset', 'today');
        $this->pageParams['statsBreakdown'] = MAX_getStoredValue('statsBreakdown', 'day');

        $this->loadParams();

        // HTML Framework
        if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
            $this->pageId = '2.1.2.2.2';
            $this->pageSections = array('2.1.2.2.1', '2.1.2.2.2');
        } elseif (phpAds_isUser(phpAds_Client)) {
            $this->pageId = '1.2.2.4';
            $this->pageSections[] = '1.2.2.1';
            if (phpAds_isAllowed(phpAds_ModifyBanner)) {
                $this->pageSections[] = '1.2.2.2';
            }
            $this->pageSections[] = '1.2.2.4';
        }

        $this->addBreadcrumbs('banner', $adId);

        // Add context
        $this->pageContext = array('banners', $adId);

        // Add shortcuts
        if (!phpAds_isUser(phpAds_Client)) {
            $this->addShortcut(
                $GLOBALS['strClientProperties'],
                'advertiser-edit.php?clientid='.$advertiserId,
                'images/icon-advertiser.gif'
            );
        }
        $this->addShortcut(
            $GLOBALS['strCampaignProperties'],
            'campaign-edit.php?clientid='.$advertiserId.'&campaignid='.$placementId,
            'images/icon-campaign.gif'
        );
        $this->addShortcut(
            $GLOBALS['strBannerProperties'],
            'banner-edit.php?clientid='.$advertiserId.'&campaignid='.$placementId.'&bannerid='.$adId,
            'images/icon-banner-stored.gif'
        );
        $this->addShortcut(
            $GLOBALS['strModifyBannerAcl'],
            'banner-acl.php?clientid='.$advertiserId.'&campaignid='.$placementId.'&bannerid='.$adId,
            'images/icon-acl.gif'
        );

        // Fix entity links
        $this->entityLinks['p'] = 'stats.php?entity=banner&breakdown=affiliate-history';
        $this->entityLinks['z'] = 'stats.php?entity=banner&breakdown=zone-history';

        // Use the day span selector
        $this->initDaySpanSelector();

        $this->hideInactive = MAX_getStoredValue('hideinactive', ($pref['gui_hide_inactive'] == 't'));
        $this->showHideInactive = true;

        $this->startLevel = MAX_getStoredValue('startlevel', 0);

        // Init nodes
        $this->aNodes   = MAX_getStoredArray('nodes', array());
        $expand         = MAX_getValue('expand', '');
        $collapse       = MAX_getValue('collapse');

        // Adjust which nodes are opened closed...
        MAX_adjustNodes($this->aNodes, $expand, $collapse);

        $aParams = array();
        $aParams['ad_id']  = $adId;

        switch ($this->startLevel)
        {
            case 1:
                $this->entities = $this->getZones($aParams, $this->startLevel, $expand, true);
                break;
            default:
                $this->startLevel = 0;
                $this->entities = $this->getPublishers($aParams, $this->startLevel, $expand);
                break;
        }

        $this->summarizeTotals($this->entities);

        $this->showHideLevels = array();
        switch ($this->startLevel)
        {
            case 1:
                $this->showHideLevels = array(
                    0 => array('text' => $GLOBALS['strShowParentAffiliates'], 'icon' => 'images/icon-affiliate.gif'),
                );
                $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveZonesHidden']}";
                break;
            case 0:
                $this->showHideLevels = array(
                    1 => array('text' => $GLOBALS['strHideParentAffiliates'], 'icon' => 'images/icon-affiliate-d.gif'),
                );
                $this->hiddenEntitiesText = "{$this->hiddenEntities} {$GLOBALS['strInactiveAffiliatesHidden']}";
                break;
        }


        // Save prefs
        $this->pagePrefs['startlevel']     = $this->startLevel;
        $this->pagePrefs['nodes']          = implode (",", $this->aNodes);
        $this->pagePrefs['hideinactive']   = $this->hideInactive;
    }

}

?>
