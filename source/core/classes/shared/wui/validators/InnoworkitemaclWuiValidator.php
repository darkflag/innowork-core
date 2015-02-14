<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Innowork.
 *
 * The Initial Developer of the Original Code is Innomatic Company.
 * Portions created by the Initial Developer are Copyright (C) 2002-2009
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *   Alex Pagnoni <alex.pagnoni@innomatic.io>
 *
 * ***** END LICENSE BLOCK ***** */

require_once('innomatic/wui/Wui.php');
if ((isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype'])
    or isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclmode'])
    or (isset(Wui::instance('wui')->parameters['wui']['wui']['evn'])
    and (Wui::instance('wui')->parameters['wui']['wui']['evn'] == 'innoworkacladd'
    or Wui::instance('wui')->parameters['wui']['wui']['evn'] == 'innoworkaclremove')))
) {
    require_once('innowork/core/InnoworkAcl.php');
    $acl = new InnoworkAcl(
    	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
    	\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
    	Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype'],
    	Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']);

    if (isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype'])) {
        $acl->setType(Wui::instance('wui')->parameters['wui']['wui']['evd']['acltype']);
        $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = Wui::instance('wui')->parameters['wui']['wui']['evd']['acltype'];
    }

    if (isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclmode'])) {
        require_once('shared/wui/WuiSessionkey.php');
        $acl_mode_sk = new WuiSessionKey('innowork_acl_mode', array('value' => Wui::instance('wui')->parameters['wui']['wui']['evd']['aclmode'], 'sessionobjectnopage' => 'true'));
    }

    if (isset(Wui::instance('wui')->parameters['wui']['wui']['evn'])) {
        switch (Wui::instance('wui')->parameters['wui']['wui']['evn']) {
            case 'innoworkacladd' :
                $tmp_innoworkcore = InnoworkCore::instance('\Innowork\Core\InnoworkCore', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess());
                $summaries = $tmp_innoworkcore->getSummaries();
                $class_name = $summaries[Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']]['classname'];
				if (!class_exists($class_name)) {
					break;
				}
				$tmpItem = new $class_name (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(), Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']);
                if ($tmpItem->mItemOwnerId == \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
                    or User::isAdminUser(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())
                    or $acl->checkPermission('', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()) >= InnoworkAcl::PERMS_RESPONSIBLE) {
                    foreach (Wui::instance('wui')->parameters['wui']['wui']['evd']['limitedacl'] as $item) {
                        switch (substr($item, 0, 1)) {
                            case 'g' :
                                if (!isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclperms'])) {
                                    Wui::instance('wui')->parameters['wui']['wui']['evd']['aclperms'] = InnoworkAcl::PERMS_ALL;
                                }
                                //$acl->removePermission( str_replace( 'g', '', $item ), '' );
                                $acl->setPermission(str_replace('g', '', $item), '', Wui::instance('wui')->parameters['wui']['wui']['evd']['aclperms']);
                                $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = true;
                                break;
                            case 'u' :
                                if (!isset(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclperms'])) {
                                    Wui::instance('wui')->parameters['wui']['wui']['evd']['aclperms'] = InnoworkAcl::PERMS_ALL;
                                }

                                //$acl->removePermission( '', str_replace( 'u', '', $item ) );
                                $acl->setPermission('', str_replace('u', '', $item), Wui::instance('wui')->parameters['wui']['wui']['evd']['aclperms']);
                                $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = true;
                                break;
                        }
                    }
                }
                break;

            case 'innoworkaclremove' :
                $tmp_innoworkcore = InnoworkCore::instance('\Innowork\Core\InnoworkCore', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess());
                $summaries = $tmp_innoworkcore->getSummaries();
                $class_name = $summaries[Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']]['classname'];
				if (!class_exists($class_name)) {
					break;
				}
				$tmpItem = new $class_name (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(), Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']);
                if ($tmpItem->mItemOwnerId == \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
                    or User::isAdminUser(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())
                    or $acl->checkPermission('', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()) >= InnoworkAcl::PERMS_RESPONSIBLE) {
                    foreach (Wui::instance('wui')->parameters['wui']['wui']['evd']['limitedacl'] as $item) {
                        switch (substr($item, 0, 1)) {
                            case 'g' :
                                //$acl->removePermission( str_replace( 'g', '', $item ), '' );
                                $acl->setPermission(str_replace('g', '', $item), '', InnoworkAcl::PERMS_NONE);
                                $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = true;
                                break;
                            case 'u' :
                                //$acl->removePermission( '', str_replace( 'u', '', $item ) );
                                $acl->setPermission('', str_replace('u', '', $item), InnoworkAcl::PERMS_NONE);
                                $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = true;
                                break;
                        }
                    }
                }
                break;

            case 'innoworkconvert' :
                $tmp_innoworkcore = InnoworkCore::instance('\Innowork\Core\InnoworkCore', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess());
                $summaries = $tmp_innoworkcore->getSummaries();
                $class_name = $summaries[Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']]['classname'];
				if (!class_exists($class_name)) {
					break;
				}
				$tmp_class = new $class_name (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(), Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']);

                if ($tmp_class->mConvertible) {
                    $tmp_data = $tmp_class->getItem(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());
                    $tmp_class->convertTo(Wui::instance('wui')->parameters['wui']['wui']['evd']['type']);
                    unset($tmp_data);
                    unset($tmp_class);
                    $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = true;
                }

                break;

            case 'innoworkaddtoclipping' :
                require_once('innowork/core/clipping/InnoworkClipping.php');
                $tmp_innoworkclipping = new InnoworkClipping(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(), Wui::instance('wui')->parameters['wui']['wui']['evd']['clippingid']);
                $tmp_innoworkclipping->addItem(Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype'], Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']);

                unset($tmp_innoworkclipping);
                $GLOBALS['innoworkcore']['itemacl'][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemtype']][Wui::instance('wui')->parameters['wui']['wui']['evd']['aclitemid']] = true;
                break;
        }
    }
}
