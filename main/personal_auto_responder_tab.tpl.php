<?php include(erLhcoreClassDesign::designtpl('lhuser/menu_tabs/personal_auto_responder_tab_pre.tpl.php'));?>
<?php if ($user_menu_tabs_personal_auto_responder_tab == true && erLhcoreClassUser::instance()->hasAccessTo('lhuser','personalautoresponder')) : ?>
    <li role="presentation" class="nav-item"><a class="nav-link <?php if ($tab == 'tab_autoresponder') : ?>active<?php endif;?>" href="#autoresponder" aria-controls="autoresponder" role="tab" data-bs-toggle="tab"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/account','Personal auto responder');?></a></li>
<?php endif;?>