<?php $this->layout('layout'); ?>

<div class="mdReadme">
    <?php echo $mdReadme; ?>
</div>

<div class="upload">
    <h3>Upload der CSV-Datei bzw. der strukturierten Textdatei</h3>
<form action="?upload" method="POST" enctype="multipart/form-data">
    <fieldset>
        <input type="file" name="file" required>
        <input type="submit">
    </fieldset>
</form>
</div>

<div class="mdDetails">
    <?php echo $mdDetails; ?>
</div>
