<?php if ($unreadTabEnabled == true) : ?>			
    <?php /*<div class="card-header" ng-if="unread_chats.list.length > 0"><a class="title-card-header" href="<?php echo erLhcoreClassDesign::baseurl('chat/list')?>/(hum)/1"><i class="material-icons chat-unread">chat</i><span class="d-none d-lg-inline"><?php include(erLhcoreClassDesign::designtpl('lhchat/lists_panels/titles/unread_chats.tpl.php'));?></span> ({{unread_chats.list.length}}{{unread_chats.list.length == 10 ? '+' : ''}})</a><a title="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('pagelayout/pagelayout','collapse/expand')?>" ng-click="lhc.toggleList('unread_chats_expanded')" class="fs24 float-end material-icons exp-cntr">{{unread_chats_expanded == true ? 'expand_less' : 'expand_more'}}</a></div>
<div ng-if="unread_chats_expanded == true" id="right-unread-chats">
	<?php include(erLhcoreClassDesign::designtpl('lhchat/lists/angular_unread_list.tpl.php'));?>
</div>*/ ?>

    <?php include(erLhcoreClassDesign::designtpl('lhfront/dashboard/panels/unread_chats.tpl.php'));?>

<?php endif;?>