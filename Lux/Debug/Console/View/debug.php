<script type="text/javascript">
$(document).ready(function() {
    $('.debug-tabs li a').click(function() {
        // remove tab highlighting
        $('.debug-tabs li a').removeClass('selected');
        
        // highlight tab
        $(this).addClass('selected');
        
        // figure out which window to show
        var debug_id = this.id.replace(/toggle-/, '');
        
        // hide all windows
        $('div.debug').hide();
        
        // show only clicked
        $('#' + debug_id).show();
        
        return false;
    });
    
    // Open/close debugger dialog.
    $(document.body).append('<div class="debug-toggle"><p><?php echo $this->getText('TEXT_OPEN_DEBUG'); ?></p></div>');
    $('#debug-wrapper').append('<div class="debug-toggle"><?php echo $this->getText('TEXT_CLOSE'); ?></div>');
    $('.debug-toggle').click(function() {
        $('.debug-toggle').show();
        $(this).hide();
        $('#debug-wrapper').toggle();
    });
});
</script>

<style type="text/css">
/*-------------------------------------------------
  General
-------------------------------------------------*/
div#debug-wrapper {
    display: none;
    position: absolute;
    width: 100%;
    top: 0;
    right: 0;
}

div#debug {
    position: relative;
    top: 0;
    right: 0;
    width: auto;
    background-color: #fff;
    text-align: left;
    padding: 10px;
    margin: 10px;
    border: 5px solid #0033AA;
}

div#debug div.debug {
    display: none;
}

div.debug-toggle {
    position: absolute;
    top: 0;
    right: 0;
    background: #0033AA;
    color: #FFFFFF;
    padding: 5px 8px;
    cursor: pointer;
    cursor: hand;
}

div#debug-wrapper div.debug-toggle {
    top: 10px;
    right: 10px;
}

div.debug-toggle p {
    padding: 0;
    margin: 0;
    text-align: center;
}

/*-------------------------------------------------
  Tabs
-------------------------------------------------*/
ul.debug-tabs {
    list-style: none;
    padding: 0 0 4px 0;
    margin: 0;
    border-bottom: 1px solid #0033AA;
}

ul.debug-tabs li {
    display: inline;
    padding: 0;
    margin: 0;
}

ul.debug-tabs li a {
    padding: 5px 10px;
}

ul.debug-tabs li a.selected {
    background-color: #0033AA;
    color: #FFFFFF;
}

/*-------------------------------------------------
  Tables
-------------------------------------------------*/
table.debug {
    clear: both;
    width: 100%;
    border-top: 1px solid #DAEAF8;
    border-right: 1px solid #DAEAF8;
    margin: 1em auto;
    border-collapse: collapse;
}

table.debug td {
    background: #eee;
}

table.debug td,
table.debug th {
    color: #445563;
    border-bottom: 1px solid #DAEAF8;
    border-left: 1px solid #DAEAF8;
    padding:.5em 7px;
}

table.debug th {
    background: #E5F0FE;
    color: #497FCF;
    font-family: "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
    font-weight: bold;
    text-transform: capitalize;
}
</style>
<div id="debug-wrapper">
    <div id="debug">

        <ul class="debug-tabs">
            <li><a id="toggle-info" href=""><?php echo $this->getText('LABEL_INFO') ?></a></li>
            <li><a id="toggle-headers" href=""><?php echo $this->getText('LABEL_HEADER') ?></a></li>
            <li><a id="toggle-profile" href=""><?php echo $this->getText('LABEL_TIMER') ?></a></li>
            <li><a id="toggle-sql" href=""><?php echo $this->getText('LABEL_SQL_PROFILE') ?></a></li>
            <li><a id="toggle-super" href=""><?php echo $this->getText('LABEL_SUPER') ?></a></li>
        </ul>

        <div class="debug" id="info" style="display: inline;">
        <table class="debug">
            <tr>
                <th><?php echo $this->getText('LABEL_REQUEST') ?></th><td><?php echo strtoupper($method) ?></td>
            </tr>
        </table>
        </div>

        <div class="debug" id="profile">
            <?php
                $locale = Solar_Registry::get('locale');
            ?>
            <table class="debug">
                <tr>
                    <th><?php echo $locale->fetch('Solar_Debug_Timer', 'LABEL_NAME') ?></th>
                    <th><?php echo $locale->fetch('Solar_Debug_Timer', 'LABEL_TIME') ?></th>
                    <th><?php echo $locale->fetch('Solar_Debug_Timer', 'LABEL_DIFF') ?></th>
                    <th><?php echo $locale->fetch('Solar_Debug_Timer', 'LABEL_TOTAL') ?></th>
                    <th><?php echo $this->getText('LABEL_DIFF_PERCENT') ?></th>
                    <th><?php echo $this->getText('LABEL_DIFF_PERCENT_CUM') ?></th>
                </tr>
                <?php
                    $total = $timer[count($timer) - 1]['total'];
                    $cumulative = 0;
                    //$total = $stop['total'];
                ?>
                <?php foreach ($timer as $mark): ?>
                    <?php
                        $percent = round(($mark['diff'] / $total) * 100);
                        $cumulative += $percent;
                    ?>
                    <tr>
                        <td><?php echo $this->escape($mark['name']) ?></td>
                        <td><?php echo $mark['time'] ?></td>
                        <td><?php echo $mark['diff'] ?></td>
                        <td><?php echo $mark['total'] ?></td>
                        <td><?php echo $percent . ' %' ?></td>
                        <td><?php echo $cumulative . ' %' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        
        <div class="debug" id="sql">
            <?php if (! empty($sql_profile)): ?>
            <table class="debug">
                <tr>
                    <th><?php echo $this->getText('LABEL_SQL_TIME'); ?></th>
                    <th><?php echo $this->getText('LABEL_SQL_STRING'); ?></th>
                </tr>
                <?php foreach ($sql_profile as $query): ?>
                    <tr>
                        <th><?php echo $this->escape($query[0]) ?></th>
                        <td><pre><?php echo $this->escape($query[1]) ?></pre></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>

        <!-- Superglobals -->
        <div class="debug" id="super">
            <table class="debug">
                <?php foreach ($super as $name => $data): ?>
                    <tr>
                        <th><?php echo $name ?></th>
                        <td><?php array_walk($data, array('Solar', 'dump')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Headers -->
        <div class="debug" id="headers">
            <h2><?php echo $this->getText('LABEL_HEADER_REQ') ?></h2>
            <table class="debug">
                <?php foreach ($headers_request as $name => $data): ?>
                    <tr>
                        <th><?php echo $name ?></th>
                        <td><?php echo $this->escape($data) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <h2><?php echo $this->getText('LABEL_HEADER_RES') ?></h2>
            <table class="debug">
                <?php foreach ($headers_response as $name => $data): ?>
                    <tr>
                        <th><?php echo $name ?></th>
                        <td><?php echo $this->escape($data) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
    <!-- End #debug -->
    </div>
</div>