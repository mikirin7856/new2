<?php /*
<?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/bodies/unread_chats.tpl.php'));?>

<div ng-if="unread_chats.list.length == 0" class="m-1 alert alert-light"><i class="material-icons">search</i><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('pagelayout/pagelayout','Nothing found...')?></div>
*/ ?>

<?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/unread_chats.tpl.php'));?>