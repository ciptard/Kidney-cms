Add search term:
<select id="search" name="search"> 
        <option value="">----</option>
        <option value="keyword">Keyword</option> 
        <option value="title">Title</option> 
        <option value="basic">Search</option> 
        <option value="by">Author</option> 
        <option value="before">Before</option> 
        <option value="after">After</option> 
        <option value="on">On</option> 
</select> 
<div class="keyword search-form-part">
        Keyword Search
        <input type="search"  name="keywords"/>
</div>
<div class="title search-form-part">
        Title Search
        <input type="search"  name="title"/>
</div>
<div class="basic search-form-part">
        Basic Search
        <input type="search"  name="basic"/>
</div>
<div class="by search-form-part">
        Author
        <input type="search"  name="by"/>
</div>
<div class="before search-form-part">
        Before
        <input type="text" class="auto-kal" class="before" name="before"/>
</div>
<div class="after search-form-part">
        After
        <input type="text" class="auto-kal" class="after" name="after"/>
</div>
<div class="on search-form-part">
        On
        <input type="text" class="auto-kal" class="on" name="on"/>
</div>
<style type="text/css">
.search-form-part{
        display:none;
}</style>
<script type="text/javascript">
$('#search').change(function() {
  $('.'+$(this).val()).show("fast");
});
function searchClick(){
    var location='<?php echo $baseUrl;?>/index.php/search';
    $(".search-form-part input").each(function(){
        if($(this).attr("class")=='auto-kal'){
            if($(this).datepicker("getDate")!=''){
                location+='/'+$(this).attr("name")+'/'+$(this).datepicker("getDate");
            }
        }else{
            if($(this).val()!=''){
                location+='/'+$(this).attr("name")+'/'+$(this).val()+'/';
            }
        }
    });
    window.location.href=location;
};
$('.auto-kal').datepicker({ dateFormat: 'MM dd yy' });
</script>
<a href="#" onclick="searchClick()" id="search-btn" class="btn">Search</a>