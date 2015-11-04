<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/admin/project.php
 *  \ingroup    project
 *  \brief      Page to setup project module
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';     // Used on dev env only
if(strpos($_SERVER['PHP_SELF'], 'dolibarr_min')>0 && !$res && file_exists("/var/www/dolibarr_min/htdocs/main.inc.php")) $res=@include "/var/www/dolibarr_min/htdocs/main.inc.php";     // Used on dev env only
else if (! $res && file_exists("/var/www/dolibarr/htdocs/main.inc.php")) $res=@include '/var/www/dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';

$langs->load("admin");
$langs->load("errors");
$langs->load("other");
$langs->load("timesheet@timesheet");
        
if (!$user->admin) {
    $accessforbidden = accessforbidden("you need to be admin");           
}
$action = GETPOST('action','alpha');
$timetype=TIMESHEET_TIME_TYPE;
$hoursperday=TIMESHEET_DAY_DURATION;
$hidedraft=TIMESHEET_HIDE_DRAFT;
$hideref=TIMESHEET_HIDE_REF;
$hidezeros=TIMESHEET_HIDE_ZEROS;
$headers=TIMESHEET_HEADERS;
$whiteListMode=TIMESHEET_WHITELIST_MODE;
$whiteList=TIMESHEET_WHITELIST;
$dropdownAjax=MAIN_DISABLE_AJAX_COMBOX;
switch($action)
{
    case save:
        if(GETPOST('timeType','alpha')==''){ // if no POST data
           break;
        }
        //general option
        $timetype=GETPOST('timeType','alpha');
        $hoursperday=GETPOST('hoursperday','alpha');
        $hidedraft=GETPOST('hidedraft','alpha');
        $hidezeros=GETPOST('hidezeros','alpha');
        $hideref=GETPOST('hideref','alpha');        
        $whiteListMode=GETPOST('blackWhiteListMode','int');
        $whiteList=GETPOST('blackWhiteList','int');
        $dropdownAjax=GETPOST('dropdownAjax','int');
        $res=dolibarr_set_const($db, "TIMESHEET_TIME_TYPE", $timetype, 'chaine', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        $res=dolibarr_set_const($db, "TIMESHEET_DAY_DURATION", $hoursperday, 'chaine', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        $res=dolibarr_set_const($db, "TIMESHEET_HIDE_DRAFT", $hidedraft, 'chaine', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        $res=dolibarr_set_const($db, "TIMESHEET_HIDE_ZEROS", $hidezeros, 'chaine', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        $res=dolibarr_set_const($db, "TIMESHEET_HIDE_REF", $hideref, 'chaine', 0, '', $conf->entity);
        if (! $res > 0) $error++;
         $res=dolibarr_set_const($db, "TIMESHEET_WHITELIST_MODE", $whiteList?$whiteListMode:2, 'int', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        $res=dolibarr_set_const($db, "TIMESHEET_WHITELIST", $whiteList, 'int', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        $res=dolibarr_set_const($db, "MAIN_DISABLE_AJAX_COMBOX", $dropdownAjax, 'int', 0, '', $conf->entity);
        if (! $res > 0) $error++;
        //headers handling
        $showProject=GETPOST('showProject','int');
        $showTaskParent=GETPOST('showTaskParent','int');
        $showTasks=GETPOST('showTasks','int');
        $showDateStart=GETPOST('showDateStart','int');
        $showDateEnd=GETPOST('showDateEnd','int');
        $showProgress=GETPOST('showProgress','int');
        $showCompany=GETPOST('showCompany','int');

        $headers=$showCompany?'Company':'';
        $headers.=$showProject?(empty($headers)?'':'||').'Project':'';
        $headers.=$showTaskParent?(empty($headers)?'':'||').'TaskParent':'';
        $headers.=$showTasks?(empty($headers)?'':'||').'Tasks':'';
        $headers.=$showDateStart?(empty($headers)?'':'||').'DateStart':'';
        $headers.=$showDateEnd?(empty($headers)?'':'||').'DateEnd':'';
        $headers.=$showProgress?(empty($headers)?'':'||').'Progress':'';
        
        $res=dolibarr_set_const($db, "TIMESHEET_HEADERS", $headers, 'chaine', 0, '', $conf->entity);
        if (! $res > 0) $error++;       
        // error handling
        
        if (! $error)
        {
            setEventMessage($langs->trans("SetupSaved"));
        }
        else
        {
            setEventMessage($langs->trans("Error"),'errors');
        }
        break;
    default:
        break;
}


/* 
 *  VIEW
 *  */
$headersT=explode('||',$headers);
foreach ($headersT as $header) {
    switch($header){
        case 'Project':
            $showProject=1;
            Break;
        case 'TaskParent':
            $showTaskParent=1;
            Break;
        case 'Tasks':
            $showTasks=1;
            Break;
        case 'DateStart':
            $showDateStart=1;
            Break;
        case 'DateEnd':
            $showDateEnd=1;
            Break;
        case 'Progress':
            $showProgress=1;
            Break;
        case 'Company':
            $showCompany=1;
            Break;
        default:
            break;
    }
    
}
//permet d'afficher la structure dolibarr
$morejs=array("/timesheet/js/timesheet.js");
llxHeader("",$langs->trans("timesheetSetup"),'','','','',$morejs,'',0,0);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("timesheetSetup"),$linkback,'title_setup');
print_titre($langs->trans("GeneralOption"));
$Form ='<form name="settings" action="?action=save" method="POST" >'."\n\t";
$Form .='<table class="noborder" width="100%">'."\n\t\t";
$Form .='<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
$Form .=$langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// type time
$Form .='<tr class="impair"><th align="left">'.$langs->trans("timeType").'</th><th align="left">'.$langs->trans("timeTypeDesc").'</th>';
$Form .='<th align="left"><input type="radio" name="timeType" value="hours" ';
$Form .=($timetype=="hours"?"checked":"").'> '.$langs->trans("hours").'<br>';
$Form .='<input type="radio" name="timeType" value="days" ';
$Form .=($timetype=="days"?"checked":"").'> '.$langs->trans("days")."</th></tr>\n\t\t";
//hours perdays
$Form .='<tr class="pair"><th align="left">'.$langs->trans("hoursperdays");
$Form .='</th><th align="left">'.$langs->trans("hoursPerDaysDesc").'</th>';
$Form .='<th align="left"><input type="text" name="hoursperday" value="'.$hoursperday;
$Form .="\" size=\"4\" ></th></tr>\n\t\t";
// hide draft
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("hidedraft");
$Form .='</th><th align="left">'.$langs->trans("hideDraftDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="hidedraft" value="1" ';
$Form .=(($hidedraft=='1')?'checked':'')."></th></tr>\n\t\t";
// hide ref
$Form .= '<tr class="pair"><th align="left">'.$langs->trans("hideref");
$Form .='</th><th align="left">'.$langs->trans("hideRefDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="hideref" value="1" ';
$Form .=(($hideref=='1')?'checked':'')."></th></tr>\n\t\t";

// hide zeros
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("hidezeros");
$Form .='</th><th align="left">'.$langs->trans("hideZerosDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="hidezeros" value="1" ';
$Form .=(($hidezeros=='1')?'checked':'')."></th></tr>\n\t</table>\n";
print $Form.'<br>';



print_titre($langs->trans("ColumnToShow"));
$Form ='<table class="noborder" width="100%">'."\n\t\t";
$Form .='<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
$Form .=$langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// Project
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("Project");
$Form .='</th><th align="left">'.$langs->trans("ProjectColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showProject" value="1" ';
$Form .=(($showProject=='1')?'checked':'')."></th></tr>\n\t\t";
// task parent
$Form .= '<tr class="pair"><th align="left">'.$langs->trans("TaskParent");
$Form .='</th><th align="left">'.$langs->trans("TaskParentColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showTaskParent" value="1" ';
$Form .=(($showTaskParent=='1')?'checked':'')."></th></tr>\n\t\t";
// task
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("Tasks");
$Form .='</th><th align="left">'.$langs->trans("TasksColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showTasks" value="1" ';
$Form .=(($showTasks=='1')?'checked':'')."></th></tr>\n\t\t";
// date de debut
$Form .= '<tr class="pair"><th align="left">'.$langs->trans("DateStart");
$Form .='</th><th align="left">'.$langs->trans("DateStartColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showDateStart" value="1" ';
$Form .=(($showDateStart=='1')?'checked':'')."></th></tr>\n\t\t";
// date de fin
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("DateEnd");
$Form .='</th><th align="left">'.$langs->trans("DateEndColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showDateEnd" value="1" ';
$Form .=(($showDateEnd=='1')?'checked':'')."></th></tr>\n\t\t";
// Progres
$Form .= '<tr class="pair"><th align="left">'.$langs->trans("Progress");
$Form .='</th><th align="left">'.$langs->trans("ProgressColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showProgress" value="1" ';
$Form .=(($showProgress=='1')?'checked':'')."></th></tr>\n\t\t";
// Company
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("Company");
$Form .='</th><th align="left">'.$langs->trans("CompanyColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showCompany" value="1" ';
$Form .=(($showCompany=='1')?'checked':'')."></th></tr>\n\t\t";
/*
// custom FIXME
$Form .= '<tr class="pair"><th align="left">'.$langs->trans("CustomCol");
$Form .='</th><th align="left">'.$langs->trans("CustomColDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="showCustomCol" value="1" ';
$Form .=(($showCustomCol=='1')?'checked':'')."</th></tr>\n\t\t";
*/
$Form .='</table>';
print $Form.'<br>';

//whitelist mode
print_titre($langs->trans("WhiteList"));
$Form ='<table class="noborder" width="100%">'."\n\t\t";
$Form .='<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
$Form .=$langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// whitelist on/off
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("blackWhiteList");
$Form .='</th><th align="left">'.$langs->trans("blackWhiteListDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="blackWhiteList" value="1" ';
$Form .=(($whiteList=='1')?'checked':'')."></th></tr>\n\t\t";
// Project
$Form .= '<tr class="pair"><th align="left">'.$langs->trans("blackWhiteListMode").'</th>';
$Form .='<th align="left">'.$langs->trans("blackWhiteListModeDesc").'</th>';
$Form .='<th align="left"><input type="radio" name="blackWhiteListMode" value="0" ';
$Form .=($whiteListMode=="0"?"checked":"").'> '.$langs->trans("modeWhiteList").'<br>';
$Form .='<input type="radio" name="blackWhiteListMode" value="1" ';
$Form .=($whiteListMode=="1"?"checked":"").'> '.$langs->trans("modeBlackList")."<br>";
$Form .='<input type="radio" name="blackWhiteListMode" value="2" ';
$Form .=($whiteListMode=="2"?"checked":"").'> '.$langs->trans("modeNone")."</th></tr>\n\t\t";
$Form .='</table><br>';


print $Form.'<br>';


// Ajax on/off

print_titre($langs->trans("Dolibarr"));
$Form ='<table class="noborder" width="100%">'."\n\t\t";
$Form .='<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
$Form .=$langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
$Form .= '<tr class="impair"><th align="left">'.$langs->trans("dropdownAjax");
$Form .='</th><th align="left">'.$langs->trans("dropdownAjaxDesc").'</th>';
$Form .= '<th align="left"><input type="checkbox" name="dropdownAjax" value="1" ';
$Form .=(($dropdownAjax=='1')?'checked':'')."></th></tr>\n\t\t";

$Form .='</table><br>';


$Form .='<input type="submit" value="'.$langs->trans('Save')."\">\n</from>";

print $Form;
llxFooter();
?>