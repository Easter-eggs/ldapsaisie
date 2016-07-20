{extends file="ls:blank.tpl"}
{block "body"}
<!-- Site wrapper -->
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src='{img name='logo-mini'}' alt='Logo' id='logo'/></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src='{img name='logo-lg'}' alt='Logo' id='logo'/></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
      {if $LSsession_subDn!=""}
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{$label_level} : {$LSsession_subDns[$LSsession_subDn]} <span class="caret"></span></a>
          <ul class="dropdown-menu">
            {foreach $LSsession_subDns as $index => $name}
            <li><a href="index.php?LSsession_topDn={$index}"{if $index==$LSsession_subDn} class="LSsession_subDn_selected"{/if}>{$name}</a></li>
            {/foreach}
            <li role="separator" class="divider"></li>
            <li><a href="index.php?LSsession_refresh">{$_refresh}</a></li>
          </ul>
        </li>
      {/if}


      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src='{img name=$LSlang}' alt='{$LSlang}' title='{$LSlang}'/> <span class="caret"></span></a>
        <ul class="dropdown-menu">
          {foreach from=$LSlanguages item=lang}
          <li><a href="index.php?lang={$lang}"><img src='{img name=$lang}' alt='{$lang}' title='{$lang}'/></a></li>
          {/foreach}
        </ul>
      </li>

      {if $displaySelfAccess}
      <li><a href='view.php?LSobject=SELF'><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <span id='user_name'>{$LSsession_username}</span></a></li>
      {/if}

      {if $displayLogoutBtn}
      <li><a href='index.php?LSsession_logout'><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></li>
      {/if}

        </ul>
      </div>
    </nav>
  </header>

  <aside class="main-sidebar">
    <section class="sidebar">
      <ul class="sidebar-menu">
      {foreach from=$LSaccess item=label key=LSobject_type}
        <li><a href='view.php?LSobject={$LSobject_type}' class='menu'>{tr msg=$label}</a></li>
      {/foreach}
      {foreach from=$LSaddonsViewsAccess item=access}
        {if $access.showInMenu}
        <li><a href='addon_view.php?LSaddon={$access.LSaddon}&view={$access.id}' class='menu'>{tr msg=$access.label}</a></li>
        {/if}
      {/foreach}
      </ul>
    </section>
  </aside>

  <div class="content-wrapper">  
{block "content"}
    <div class="content"></div>
{/block}
  </div>
  <!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->
{/block}
