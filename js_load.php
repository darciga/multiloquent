<script type="text/javascript">

var dfLoadStatus = 0;
var dfLoadFiles = [
     // - have to use the builtin jquery :| ["//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"],
     // - have to enqueue this via wp_enqueue_script ["//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"]
     ];

function loadjs() {
    if (!dfLoadFiles.length) return;

    var dfGroup = dfLoadFiles.shift();
    dfLoadStatus = 0;

    for(var i = 0; i<dfGroup.length; i++) {
        dfLoadStatus++;
        var element = document.createElement('script');
        element.src = dfGroup[i];
        element.onload = element.onreadystatechange = function() {
        if ( ! this.readyState || 
               this.readyState == 'loaded' || 
               this.readyState == 'complete') {
            dfLoadStatus--;
            if (dfLoadStatus==0) loadjs();
        }
    };
    document.body.appendChild(element);
    
  }
}

if (window.addEventListener){
    window.addEventListener("load", loadjs, false);
}
else if (window.attachEvent){
    window.attachEvent("onload", loadjs);
}
else {
    window.onload = loadjs;
}

</script>
