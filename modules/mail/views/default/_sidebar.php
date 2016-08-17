<div class="vertical-box-column width-250">
    <!-- begin wrapper -->
    <div class="wrapper bg-silver text-center">
        <a href="/mail/compose" class="btn btn-success p-l-40 p-r-40 btn-sm">
            Compose
        </a>
    </div>
    <!-- end wrapper -->
    <!-- begin wrapper -->
    <div class="wrapper">
        <p><b>FOLDERS</b></p>
        <ul class="nav nav-pills nav-stacked nav-sm">
            <li<?= $type == 0 ? ' class="active"' : '' ?>><a href="/mail/index"><i class="fa fa-inbox fa-fw m-r-5"></i> Inbox <span class="badge pull-right">52</span></a></li>
            <li<?= $type == 1 ? ' class="active"' : '' ?>><a href="/mail/index/1"><i class="fa fa-send fa-fw m-r-5"></i> Sent</a></li>
        </ul>
    </div>
</div>