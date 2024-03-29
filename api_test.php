<!doctype html>
<html>
<head>


<style>
.ui-icon-plus-green::after {
    background-image: url("./js/images/icons-png/plus-green.png");
    backdrop-filter: invert(1);
    background-size: 18px 18px;
}
.ui-icon-delete-red::after {
    background-image: url("./js/images/icons-png/delete-red.png");
    backdrop-filter: invert(1);
    background-size: 18px 18px;
}
</style>

    <title>My Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./js/jquery.mobile-1.4.5.min.css">
    <script src="./js/jquery-1.9.1.min.js"></script>
    <script src="./js/jquery.mobile-1.4.5.min.js"></script>

<script>
var id_race = 581;

$(document).ready(function(){
    getData();
});

function getData()
{
    var entry = [];
    // var list = '<ul data-role="listview" data-filter="true" data-filter-placeholder="Search fruits..." data-inset="true" data-input="#filterBasic-input">';
    var list ="";

    $.getJSON( "./api_race_entry.php?id_race="+id_race, function( data ) {
        $.each( data, function( key, val ) {
            var clas_entry = (val.kat == null)?'ui-icon-plus-green ':'ui-icon-delete-red ';
            var clas = 'ui-btn ui-btn-icon-left '+clas_entry;
            var kat = '';
            kat =  (val.kat == null)?'':val.kat;

            list = "<li><a onclick='toggleEntry(this)' id="+val.id+" href='#' class='"+clas+"'>"+kat+" "+val.name+" "+val.reg+"<span id='"+val.id+"' class='ui-btn-icon-right "+clas_entry+"'/></a></li>";
            $("#demo").append(list)
        });
    }).done(function(){
        // $("#demo").append(list);
    });
}

function toggleEntry(elem) {
    console.log("id = "+elem.id);
    var status = '1'
    $.getJSON( "./api_race_entry.php?id_race="+id_race+"&id_user_zavod="+elem.id+"&status="+status, function( data ) {
        console.log(data);
    }).done(function(result) {
        console.log(result);
    });
}

</script>

</head>
<body id="root">
    <div data-role="page">
 
        <div data-role="header">
            Zavod 581
        </div><!-- /header -->
 
        <div role="main" class="ui-content">

            <form class="ui-filterable">
                <input id="filterBasic-input" data-type="search">
            </form>
            <ul data-role="listview" data-filter="true" data-inset="true" data-input="#filterBasic-input" data-icon="true" id="demo">
            </ul>

        </div><!-- /content -->
 
        <div data-role="footer">
        </div><!-- /footer -->
 
    </div><!-- /page -->
</body>
</html>


<?
?>