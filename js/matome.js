$(window).on("load",function(){

var siteUrl = "http://192.168.56.10/stealsystem_op/";

    $(document).on("click",".threadTitle",function(){
        var threadSha = this.className.split(" ")[1];
        var rootSpan = "." + threadSha + " .responcewp";
        if(global.threadSwith[threadSha] === false){
            var data = JSON.stringify(threadSha);
            $.ajax({
                type: "POST",
                url: global.scriptUrl + "resajax.php",
                data: data,
                success: function(responce){
                    $(rootSpan).append(responce[0]);
                    for (var i=0; i < responce[2]; ++i) {
                        global.choiced[threadSha][responce[3][i]] = {"remove" : false, "name" : null};
                    }
                    $(rootSpan).append(responce[1]);
                    global.threadSwith[threadSha] = true;
                }
            });
        }else{
            $("." + threadSha + " .responcewp").toggle(function(){
            },function(){
            });
        }
    });

    $(document).on("click",".responce",function(){
        var threadSha = this.className.split(" ")[0];
        var resSha = this.className.split(" ")[1];
        // var cerecter = "." + threadSha + " ." + resSha + " .responce";
        if(global.choiced[threadSha][resSha].remove === false){
            global.choiced[threadSha][resSha].remove = true;
            $(this).css("background-color","#cccccc");
        }else{
            global.choiced[threadSha][resSha].remove = false;
            $(this).css("background-color","#ffffff");
        }
    });

    $(document).on("click",".threadbotton",function(){
        var threadSha = this.className.split(" ")[1];
        $("." + threadSha).css("background-color","#dcdcdc");
        var reTitle = $(":text[name=" + threadSha + "]").val();
        var choiced = global.choiced[threadSha];
        var reName;
        //TODO HERE!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // for (var resSha in choiced) {
        //     if(resSha !== "reTitle"){
        //         reName = $(":text[name=" + resSha + "]").val();
        //         global.choiced[threadSha][resSha].name = reName;
        //     }
        // }
        var ar = global.categorychoiced[threadSha];
        var data = JSON.stringify({threadsha:threadSha, choiced:global.choiced[threadSha], retitle:reTitle, category:ar});

        $.ajax({
            type: "POST",
            url: global.scriptUrl + "articleajax.php",
            data: data,
            success: function(responce){
                $("." + threadSha + " .html").append(responce);
            },
            complete: function(){
                $("." + threadSha).css("background-color","#ffffff");
            }
        });
    });
    // $(document).on("click",".threadbotton",function(){
    //     var threadSha = this.className.split(" ")[1];
    //     $("." + threadSha).css("background-color","#dcdcdc");
    //     var reTitle = $(":text[name=" + threadSha + "]").val();
    //     var choiced = global.choiced[threadSha];
    //     var reName;
    //     for (var resSha in choiced) {
    //         if(resSha !== "reTitle"){
    //             reName = $(":text[name=" + resSha + "]").val();
    //             global.choiced[threadSha][resSha].name = reName;
    //         }
    //     }
    //     var ar = global.categorychoiced[threadSha];
    //     var data = JSON.stringify({threadsha:threadSha, choiced:global.choiced[threadSha], retitle:reTitle, category:ar});
    //
    //     $.ajax({
    //         type: "POST",
    //         url: global.scriptUrl + "articleajax.php",
    //         data: data,
    //         success: function(responce){
    //             $("." + threadSha + " .html").append(responce);
    //         },
    //         complete: function(){
    //             $("." + threadSha).css("background-color","#ffffff");
    //         }
    //     });
    // });

    $(document).on("click",".category",function(){
        var threadSha = this.className.split(" ")[0];
        var categoryName = $(this).text();
        var categoryID = global.category[categoryName];
        if(global.categorychoiced[threadSha].indexOf(categoryID) === -1){
            $(this).css("background-color","#cccccc");
            global.categorychoiced[threadSha].push(categoryID);
        }else{
            var index = global.categorychoiced[threadSha].indexOf(categoryID);
            global.categorychoiced[threadSha].splice(index, 1);
            $(this).css("background-color","#ffffff");
        }
    });

    $(document).on("click",".deleted",function(){
        var threadSha = this.className.split(" ")[0];
        if(global.deleted[threadSha] === false){
            global.deleted[threadSha] = true;
            console.log(global.deleted[threadSha]);
            $(this).css("background-color","#cccccc");
        }else{
            global.deleted[threadSha] = false;
            console.log(global.deleted[threadSha]);
            $(this).css("background-color","#ffffff");
        }
    });

    $(document).on("click",".submited",function(){
        var threadSha = this.className.split(" ")[0];
        var data = JSON.stringify({thread_sha:threadSha});
        if(global.deleted[threadSha] === true){
            $("."+threadSha + " .submited").css("background-color","#cccccc");
            $.ajax({
                type: "POST",
                data: data,
                url: global.scriptUrl + "deleteajax.php",
                success: function(responce){
                    console.log(responce);
                    $("."+threadSha + " .submited").css("background-color","#aa0000");
                },
                complete:function(){
                    $("."+threadSha + " .submited").css("background-color","#aa0000");
                }
            });
        }
    });

    $(document).on("click",".manualcensor",function(){
        var threadSha = this.className.split(" ")[0];
        var resSha = this.className.split(" ")[1];
        var censorWord = $(":text[name=" + resSha + "word]").val();
        var beCensor   = $(":text[name=" + resSha + "becensor]").val();
        var isInsert   = $("[name=" + resSha + "swich]:checked").val();
        var data = JSON.stringify({res_sha : resSha, censor_word : censorWord, be_censor : beCensor, is_insert : isInsert});
        $("." + resSha + " .manualcensor").css("background-color","#cccccc");
        $("#" + resSha).css("background-color","#cccccc");
        
        $.ajax({
            type: "POST",
            data: data,
            url: global.scriptUrl + "resbodymanualcensorajax.php",
            success: function(responce){
                $("#" + resSha).html(responce);
                $("#" + resSha).css("background-color","#ffffff");
                $("." + resSha + " .manualcensor").css("background-color","#ffffff");
            },
        });
    });
});
