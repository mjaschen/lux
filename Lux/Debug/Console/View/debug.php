<script type="text/javascript">
$(document).ready(function() {
    $('.debug-tabs li a').click(function() {
        $('.debug-tabs li a').removeClass('selected');
        $(this).addClass('selected');

        var debug_id = this.id.replace(/toggle-/, '');
        $('div.debug').hide();
        $('#' + debug_id).show();

        return false;
    });

    // Open/close debugger dialog.
    $(document.body).append('<div class="debug-toggle"><p>open debugger</p></div>');
    $('#debug-wrapper').append('<div class="debug-toggle"><p>close</p></div>');

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
    height: 500px;
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

table.debug tr.odd td,
table.debug thead td {
    background: #F0F6FF;
}

table.debug .column1	{
    background: #F9FCFE;
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
}
</style>
<div id="debug-wrapper">
    <div id="debug">

        <ul class="debug-tabs">
            <li><a id="toggle-info" href="">Info</a></li>
            <li><a id="toggle-profile" href="">Profiler</a></li>
            <li><a id="toggle-sql" href="">SQL profiling</a></li>
            <li><a id="toggle-super" href="">Superglobals</a></li>
            <li><a id="toggle-headers" href="">Headers</a></li>
        </ul>

        <div class="debug" id="info">
        <table class="debug">
            <tr>
                <th><?php echo $this->getText('LABEL_REQUEST') ?></th><td><?php echo strtoupper($method) ?></td>
            </tr>
        </table>
        </div>

        <div class="debug" id="profile">
        <table class="debug">
            <tr>
                <th><?php echo $this->getText('LABEL_NAME') ?></th>
                <th><?php echo $this->getText('LABEL_TIME') ?></th>
                <th><?php echo $this->getText('LABEL_DIFF') ?></th>
                <th><?php echo $this->getText('LABEL_TOTAL') ?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($timer as $mark):
                $class = $i == 0 ? '' : ' class="odd"';
                $i = $i == 0 ? 1 : 0;
            ?>
                <tr<?php echo $class; ?>>
                    <td><?php echo $this->escape($mark['name']) ?></td>
                    <td><?php echo $mark['time'] ?></td>
                    <td><?php echo $mark['diff'] ?></td>
                    <td><?php echo $mark['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>

        <div class="debug" id="sql">

        <?php if (! empty($sql_profile)): ?>
        <table class="debug">
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
            <?php
            $i = 0;
            foreach ($super as $name => $data):
                $class = $i == 0 ? '' : ' class="odd"';
                $i = $i == 0 ? 1 : 0;
            ?>
                <tr<?php echo $class; ?>>
                    <td><?php echo $name ?></td>
                    <td><?php array_walk($data, array('Solar', 'dump')); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>

        <!-- Headers -->
        <div class="debug" id="headers">
        <table class="debug">
            <?php
            $i = 0;
            foreach ($headers_request as $name => $data):
                $class = $i == 0 ? '' : ' class="odd"';
                $i = $i == 0 ? 1 : 0;
            ?>
                <tr<?php echo $class; ?>>
                    <th><?php echo $name ?></th>
                    <td><?php echo $this->escape($data) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <table class="debug">
            <?php
            $i = 0;
            foreach ($headers_response as $name => $data):
                $class = $i == 0 ? '' : ' class="odd"';
                $i = $i == 0 ? 1 : 0;
            ?>
                <tr<?php echo $class; ?>>
                    <th><?php echo $name ?></th>
                    <td><?php echo $this->escape($data) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        </div>

        <div class="debug" id="headers-response">
        </div>

    </div>
</div>