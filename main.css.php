<?
	header('Content-Type: text/css; charset=UTF-8');
	require_once('cfg/_colors.php');
	require_once('cfg/_cfg.php');
?>
/*	members - online prihlaskovy system */

body, html {
	font-family : Verdana, Arial, sans-serif;
	font-size : 10pt;
	color: <? echo $g_colors['body_text']; ?>;
	background-color : <? echo $g_colors['body_bgcolor']; ?>;
	margin-top: 0px;
}

TD, A, P {
	font-family : Verdana, Arial, sans-serif;
	font-size : 10pt;
}

HR {
	width : 95%;
	height : 2px;
	color: <? echo $g_colors['body_hr_line']; ?>;
	background-color: <? echo $g_colors['body_hr_line']; ?>;
	border: 0px;
}

HR.nav {
	width : 80%;
	height : 3px;
	color: <? echo $g_colors['nav_hr_line']; ?>;
	background-color: <? echo $g_colors['nav_hr_line']; ?>;
	border: 0px;
}

.HdrClubName {
	font-size : 7pt;
	color : <? echo $g_colors['body_text']; ?>;
	font-weight : normal;
	text-align : right;
	padding: 2px;
	padding-right: 6px;
}

.HdrAppName {
	font-size : 7pt;
	color : <? echo $g_colors['disable_text']; ?>;
	font-weight : normal;
	text-align : left;
	padding: 2px;
	padding-left: 6px;
}

.HdrDate {
	font-size : 7pt;
	color : <? echo $g_colors['disable_text']; ?>;
	font-weight : normal;
	text-align : right;
	padding: 2px;
	padding-right: 6px;
}

H2 {
	font-variant : small-caps;
	font-size : 16pt;
	color : <? echo $g_colors['body_text']; ?>;
	font-weight : bold;
	text-align : left;
}

H3 {
	font-size : 12pt;
	color : <? echo $g_colors['body_text']; ?>;
	font-weight : bold;
	text-align : left;
}

TD.NewsItemDate, TD.NewsItemDateInt {
	width : 100px;
	vertical-align : top;
	text-align : right;
	color : <? echo $g_colors['news_item_date']; ?>;
	font-weight : bold;
}

TD.NewsItemTitle, TD.NewsItemTitleInt {
	text-align : left;
	vertical-align : top;
	color : <? echo $g_colors['news_item_title']; ?>;
	font-weight : bold;
}

TD.NewsItem, TD.NewsItemInt {
	color : <? echo $g_colors['news_item_text']; ?>;
	vertical-align : top;
	text-align : left;
}

TD.NewsItemInt {
	border-left:9px solid <? echo $g_colors['news_item_date']; ?>;
	padding-left : 6px;
}

TD.LastDate {
	font-size : 8pt;
	color : <? echo $g_colors['news_last_date']; ?>;
	vertical-align : top;
	text-align : right;
}

TD.MemberText {
	color : <? echo $g_colors['nav_member_text']; ?>;
	vertical-align : middle;
	font-weight : bold;
	text-align : center;
}

TD.DataValue {
	text-align : left;
	color : <? echo $g_colors['form_data_value']; ?>;
	font-weight : bold;
}

.NewsAutor {
	color : <? echo $g_colors['news_item_author']; ?>;
	font-weight : normal;
	font-size : 8pt;
}

A:LINK {
	color : <? echo $g_colors['body_link']; ?>;
	font-weight : bold;
	text-decoration : none;
}

A:VISITED {
	color: <? echo $g_colors['body_link_visited']; ?>;
	font-weight : bold;
	text-decoration : none;
}

A:HOVER {
	color: <? echo $g_colors['body_link_hover']; ?>;
	font-weight : bold;
	text-decoration : underline;
}

A.NaviColSm:LINK, A.NaviColSm:VISITED {
	color : <? echo $g_colors['nav_link']; ?>;
	font-weight : bold;
	text-decoration : none;
}

A.NaviColSm:HOVER {
	color: <? echo $g_colors['nav_link_hover']; ?>;
	font-weight : bold;
	text-decoration : underline;
}

.NaviColSmSel {
	color : <? echo $g_colors['nav_item_selected']; ?>;
	font-weight : bold;
	text-decoration : none;
	border-left:2px dotted <? echo $g_colors['nav_item_selected_border']; ?>;
	border-right:2px dotted <? echo $g_colors['nav_item_selected_border']; ?>;
	background-color : <? echo $g_colors['nav_bgcolor_item_selected']; ?>;
	padding-left: 4px;
	padding-right: 4px;
}

.NaviGroup {
	color : <? echo $g_colors['nav_group_header']; ?>;
	font-weight : bold;
	text-decoration : none;
}

A.NewsEdit:LINK, A.NewsEdit:VISITED {
	font-weight : normal;
	text-decoration : none;
}

A.NewsEdit:HOVER {
	font-weight : normal;
	text-decoration : underline;
}

A.NewsErase:LINK, A.NewsErase:VISITED {
	color :  <? echo $g_colors['erase_link']; ?>;
	font-weight : normal;
	text-decoration : none;
}

A.NewsErase:HOVER {
	color:  <? echo $g_colors['erase_link']; ?>;
	font-weight : normal;
	text-decoration : underline;
}

A.Erase:LINK, A.Erase:VISITED {
	color : <? echo $g_colors['erase_link']; ?>;
	font-weight : bold;
	text-decoration : none;
}

A.Erase:HOVER {
	color: <? echo $g_colors['erase_link']; ?>;
	font-weight : bold;
	text-decoration : underline;
}
A.Highlight:LINK, A.Highlight:VISITED {
	color : <? echo $g_colors['highlight_text']; ?>;
	font-weight : bold;
	text-decoration : none;
}

A.Highlight:HOVER {
	color: <? echo $g_colors['highlight_text']; ?>;
	font-weight : bold;
	text-decoration : underline;
}

.Highlight {
	color : <? echo $g_colors['highlight_text']; ?>;
	font-weight : bold;
	text-decoration : none;
}
/* new > ------------ */
.DisableText {
	color : <? echo $g_colors['disable_text']; ?>;
}

.WarningText {
	color : <? echo $g_colors['warning_text']; ?>;
}

.Footer {
	color : <? echo $g_colors['footer_text']; ?>;
	font-size : 9pt;
}

A.Footer:LINK, A.Footer:VISITED {
	color : <? echo $g_colors['footer_text']; ?>;
	font-weight : bold;
	text-decoration : none;
	font-size : 9pt;
}

A.Footer:HOVER {
	color: <? echo $g_colors['footer_text']; ?>;
	font-weight : bold;
	text-decoration : underline;
	font-size : 9pt;
}

.VersionText {
	color: <? echo $g_colors['version_text']; ?>;
	font-size : 7pt;
}

.ResultText {
	color: <? echo $g_colors['result_text']; ?>;
	font-size : 12pt;
	font-weight : bold;
}

.MiniHelpText {
	color: <? echo $g_colors['disable_text']; ?>;
	font-size : 7pt;
}

.TextAlert21 {
	color: #00FF00;
}
.TextAlert7, .TextAlert, .TemporaryChip {
	color: #FFFF00;
}

.TextAlertBold {
	color: #FFFF00;
	font-weight : bold;
}

.TextAlert2 {
	color: #FF0000;
	text-decoration : blink;
}

.TextAlertExp {
	color: #666666;
}

.TextAlertExpLight {
	color: #999999;
}

.TextCheckOk {
	color: #00FF00;
}
.TextCheckBad {
	color: #FF0000;
}

.kategory_small_list {
	font-family : Arial, helvetica, sans-serif;
	font-size : 7pt;
}

/* < new ------------ */

img {
	border : 0;
}

A.adr_name:LINK , A.adr_name:VISITED {
	color : <? echo $g_colors['address_link']; ?>;
	font-weight : normal;
	text-decoration : none;
}

A.adr_name:HOVER {
	color: <? echo $g_colors['address_link_hover']; ?>;
	font-weight : normal;
	text-decoration : underline;
}

TD.login {
	color : <? echo $g_colors['nav_member_text']; ?>;
	vertical-align : middle;
	font-weight : bold;
	text-align : right;
}

INPUT.login:focus {
	background-color : <? echo $g_colors['input_bgcolor_focus']; ?>;
}

INPUT.login {
	text-align : left;
	vertical-align : middle;
	color: <? echo $g_colors['input_text']; ?>;
	background-color : <? echo $g_colors['input_bgcolor']; ?>;
	border-width: 2px;
	border-style: solid;
	border-color: <? echo $g_colors['input_border']; ?>;
	padding: 1px 4px;
}
/*
INPUT.loginsbm {
	padding: 2px 6px;
	border: 2px solid #333;
	border-left-color: #CCC;
	border-top-color: #CCC;
	background-color : <? echo $g_colors['nav_bgcolor_out']; ?>;
	color : <? echo $g_colors['nav_member_text']; ?>;
	font-weight : bold;
}

INPUT.loginsbm:focus {
	border: 2px solid #CCC;
	border-left-color: #333;
	border-top-color: #333;
	background-color : <? echo $g_colors['input_bgcolor']; ?>;
}
*/
button {
	color: ButtonText;
}

.refresh_warn {
	font-size : 8pt;
	text-align : left;
	color: #F00;
	font-weight : bold;
}
/* table - calendar */

TABLE.calendar
{
	margin-top: 2px;
	margin-bottom: 1px;
	border: 1px none;
	border-collapse: collapse;
	color: <? echo $g_colors['body_text']; ?>;
	background-color: <? echo $g_colors['body_bgcolor']; ?>;
}

TABLE.calendar TD
{
	font-size: 8pt;
	color: <? echo $g_colors['body_text']; ?>;
	background-color : <? echo $g_colors['table_cal_normal']; ?>;
	border: 1px solid;
	border-color: <? echo $g_colors['table_cal_border']; ?>;
	padding: 1px 4px;
	text-align: center;
	vertical-align: middle;
}

TABLE.calendar TD.header
{
	border-color: <? echo $g_colors['table_header']; ?>;
	color: <? echo $g_colors['table_text_header']; ?>;
	background-color: <? echo $g_colors['table_header']; ?>;
	font-weight : bold;
}

TABLE.calendar TD.empty
{
	background-color: <? echo $g_colors['table_cal_empty']; ?>;
}

TABLE.calendar TD.weekend
{
	background-color: <? echo $g_colors['table_cal_weekend']; ?>;
}

TABLE.calendar TD.days
{
	border-color: <? echo $g_colors['table_header']; ?>;
	color: <? echo $g_colors['table_text_header']; ?>;
	background-color: <? echo $g_colors['table_header']; ?>;
}

TABLE.calendar TD.race
{
	background-color: <? echo $g_colors['table_cal_race']; ?>;
}

TABLE.calendar TD.today
{
	background-color: <? echo $g_colors['table_cal_today']; ?>;
	color: <? echo $g_colors['table_cal_today_text']; ?>;
}

.amountred {
	color: red;
}

.amountgreen {
	color: green;
}

.amount {
}

.type0_Z {
	color : <? echo $g_colors['r_type0_Z']; ?>;
	font-weight : bold;
}

.type0_V {
	color : <? echo $g_colors['r_type0_V']; ?>;
	font-weight : bold;
}

.type0_S {
	color : <? echo $g_colors['r_type0_S']; ?>;
	font-weight : bold;
}

.type0_T {
	color : <? echo $g_colors['r_type0_T']; ?>;
	font-weight : bold;
}

.type0_N {
	color : <? echo $g_colors['r_type0_N']; ?>;
	font-weight : bold;
}

.type0_J {
	color : <? echo $g_colors['r_type0_J']; ?>;
	font-weight : bold;
}

.left-margin-50px {
    margin-left: 50px;
}

<?
if (!$g_is_release)
{
?>
#is_debug_info {
	color: #FF0;
	background-color: #503300;
	text-align : center;
	padding: 3px;
	font-size: 16pt;
	font-weight : bold;
}
<?
}
?>
