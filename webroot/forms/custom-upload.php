<form id='upload' action='upload.php' method='POST' enctype='multipart/form-data'>

<fieldset>
<legend>Select File</legend>

<input type='hidden' id='MAX_FILE_SIZE' name='MAX_FILE_SIZE' value='100000000' />

<div style='align:center;'>
	<label for='fileselect'></label>
	<input type='file' id='fileselect' name='fileselect[]' multiple='multiple' />
	<div style='vertical-align: middle; font-size: 16px; margin 0 auto; height: 100px; background-color: rgb(174, 176, 181); color: rgb(227, 28, 61); align: center;' id='filedrag'>or drop OSCAL file here</div>
</div>

<div id='submitbutton'>
	<button type='submit'>Open File</button>
</div>

</fieldset>

</form>
<div style='text-align:left;' id='progress'></div>

<div style='text-align:left;' id='messages'>
<script src='oscal-open.js'></script>
