<script type="text/javascript">
$(document).ready(function() {
    $('.toggle').click(function() {
        $('.debug').hide();
        $('.toggle').removeClass('selected');
        
        debug_id = this.id.replace(/toggle-/, '');
        $('#' + debug_id).toggle();
        
        $('#' + this.id).addClass('selected');
        
        return false;
    })
});
</script>

<style type="text/css">
div#debug {
    width: 100%;
    height: 500px;
    background-color: #fff;
    top: 0;
    overflow: auto;
}

div#debug ul li a.selected {
    background-color: #ccc;
}

div#debug div.debug {
    display: none;
}

div#debug div.debug table td {
    padding: 4px;
}

div#debug div.debug table th {
    background-color: #ccc;
}

div#debug ul li {
    display: inline;
}
</style>


<div id="debug">
    
    <ul style="list-style: none; display: inline;">
        <li><a class="toggle" id="toggle-info" href="">Info</a></li>
        <li><a class="toggle" id="toggle-profile" href="">Profiler</a></li>
        <li><a class="toggle" id="toggle-sql" href="">SQL profiling</a></li>
        <li><a class="toggle" id="toggle-super" href="">Superglobals</a></li>
        <li><a class="toggle" id="toggle-headers" href="">Headers</a></li>
    </ul>

    <div class="debug" id="info">
    <table>
        <tr>
            <th><?php echo $this->getText('LABEL_REQUEST') ?></th><td><?php echo strtoupper($method) ?></td>
        </tr>
    </table>
    </div>
    
    <div class="debug" id="profile">
    <table>
        <tr>
            <th><?php echo $this->getText('LABEL_NAME') ?></th>
            <th><?php echo $this->getText('LABEL_TIME') ?></th>
            <th><?php echo $this->getText('LABEL_DIFF') ?></th>
            <th><?php echo $this->getText('LABEL_TOTAL') ?></th>
        </tr>
        <?php foreach ($timer as $mark): ?>
        <tr>
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
    <table>
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
    <table>
        <?php foreach ($super as $name => $data): ?>
            <tr>
                <td><?php echo $name ?></td>
                <td><?php array_walk($data, array('Solar', 'dump')); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <!-- Headers -->
    <div class="debug" id="headers">
    <table>
        <?php foreach ($headers_request as $name => $data): ?>
            <tr>
                <th><?php echo $name ?></th>
                <td><?php echo $this->escape($data) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <table>
        <?php foreach ($headers_response as $name => $data): ?>
            <tr>
                <th><?php echo $name ?></th>
                <td><?php echo $this->escape($data) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    </div>

    <div class="debug" id="headers-response">
    </div>

</div>