//hide placeholder on form focus
$('[placeholder]').focus(placeholder_onfocus).blur(placeholder_onblur);

function placeholder_onblur() {
    $(this).attr('placeholder', $(this).attr('data-text'));
}

function placeholder_onfocus() {
    $(this).attr('data-text', $(this).attr('placeholder'));
    $(this).attr('placeholder', '');
}

// hide message box after click on x icon
$('.message-box i').click(function () {
    $('.message-box').css('display', 'none');
});

//show password after click on eye icon
$('.eye').click(function () {
    $(this).toggleClass('fa-eye');
    $(this).toggleClass('fa-eye-slash');
    if ($(this).hasClass('fa-eye-slash')) {
        $(this).siblings().attr('type', 'text');
        $(this).attr('title', 'إخفاء كلمة المرور');
    } else {
        $(this).siblings().attr('type', 'password');
        $(this).attr('title', 'إظهار كلمة المرور');
    }
});

/*
 **********************************************
 ********* Start products Page ****************
 **********************************************
 */
$(document).on('click', '.prod-card .love-span', function () {
    //get prodID
    let prodID = $(this).parent().data('prodid');
    // store $(this) in var to use it in $.ajax
    let thisSpan = $(this);
    if (thisSpan.hasClass('love-span-clicked')) {
        // request to remove the product from favorite
        //send ajax request to DB
        $.ajax({
            method: 'GET',
            url: 'fetchProduct.php',
            data: { do: 'favProduct', action: 'remove', prodID: `${prodID}` },
            success: function (res) {
                let result = JSON.parse(res);
                if (result['success'] == true) {
                    //the request complete, change the look of the icon
                    thisSpan.find('i').removeClass('fa');
                    thisSpan.find('i').addClass('far');
                    thisSpan.removeClass('love-span-clicked');
                } else {
                    //the request did not complete, alert the message
                    alert(result['message']);
                }
            },
        });
    } else {
        // request to add the product to favorite
        //send ajax request to DB
        $.ajax({
            method: 'GET',
            url: 'fetchProduct.php',
            data: { do: 'favProduct', action: 'add', prodID: `${prodID}` },
            success: function (res) {
                let result = JSON.parse(res);
                if (result['success'] == true) {
                    //the request complete, change the look of the icon
                    thisSpan.find('i').removeClass('far');
                    thisSpan.find('i').addClass('fa');
                    thisSpan.addClass('love-span-clicked');
                } else {
                    //the request did not complete, alert the message
                    alert(result['message']);
                }
            },
        });
    }
});
$(document).on('click', '.prod-card .like-span', function () {
    //get prodID
    let prodID = $(this).parent().data('prodid');
    // store $(this) in var to use it in $.ajax
    let thisSpan = $(this);
    if (thisSpan.hasClass('like-span-clicked')) {
        // request to remove the like
        //send ajax request to DB
        $.ajax({
            method: 'GET',
            url: 'fetchProduct.php',
            data: { do: 'likeProduct', action: 'remove', prodID: `${prodID}` },
            success: function (res) {
                console.log(res);
                let result = JSON.parse(res);
                if (result['success'] == true) {
                    //the request complete, change the look of the icon
                    thisSpan.removeClass('like-span-clicked');
                } else {
                    //the request did not complete, alert the message
                    alert(result['message']);
                }
            },
        });
    } else {
        // request to remove the like
        //send ajax request to DB
        $.ajax({
            method: 'GET',
            url: 'fetchProduct.php',
            data: { do: 'likeProduct', action: 'add', prodID: `${prodID}` },
            success: function (res) {
                console.log(res);
                let result = JSON.parse(res);
                if (result['success'] == true) {
                    //the request complete, change the look of the icon
                    thisSpan.addClass('like-span-clicked');
                } else {
                    //the request did not complete, alert the message
                    alert(result['message']);
                }
            },
        });
    }
});
// ********* End products Page ****************

/*
 **********************************************
 ********* Start add product section **********
 **********************************************
 */

/*
 ** Function to change image source after file upload
 */
function changeImage(input) {
    document.getElementById('output').src = window.URL.createObjectURL(
        input.files[0]
    );
    $('.output-box i').css('z-index', '-5');
}

//NOTE: I use document because JQuery does not work with new elements
//document help to applay the function on already existed elements & new added elements

/*
 ** Variables to store and control product colors and sizes
 */
let MAX_SIZES = 9;
//count for added sizes
let sizes_count = 1;
//Array to store selected colors for each size
//first dimension (rows) for product sizes, second one (columns) for product colors;
var selectColors = new Array(MAX_SIZES);
selectColors[sizes_count - 1] = new Array();

/*
 ** Function to change SelectSizeElement options
 ** According to product classification
 */
// Variable to store selected sizes
let selected_sizes = new Array();
selected_sizes.push($('option:selected', '.prod-size').attr('value'));
$('option:selected', '.prod-size').attr('data-selected', 'true');

// Function Start
$(document).on('change', '.prod-category', function () {
    //get the size type from the selected option
    let selected_size_type = $('option:selected', this).attr('data-sizeType');
    //get all basic options to pick from them options with  same as selected_size_type
    let all_size_options = $('#basic-size-options').find('option');

    // Check if sizes_count more than the sizes available for this category
    //First get the available sizes number {and store this sizes in array available_sizes}
    let available_sizes = new Array();
    $.each(all_size_options, function (index, size_option) {
        //if this option match selected_size_type (for the selected category)
        //then add it to the select
        if ($(size_option).attr('data-sizeType') == selected_size_type) {
            available_sizes.push(size_option);
        }
    });
    MAX_SIZES = available_sizes.length;
    // delete sizes if their number greater than MAX_SIZES
    // delete all size except the basic one with the class "parent-0"
    if (sizes_count > MAX_SIZES) {
        for (let i = sizes_count - 1; i > 0; i--) {
            $(`#parent-${i}`).remove();
        }
        sizes_count = 1;
    }
    //get all size select elements
    let all_size_selects = $('.add-prod-container').find(
        'select[class="prod-size"]'
    );
    //counter to set differnet default option for each sizeSelect
    let idx = 0;
    //cleare selected_sizes
    selected_sizes = [];
    //foreach select of all_size_selects update its options to match selected category
    $.each(all_size_selects, function (i, size_select) {
        //first cleare old options
        size_select.innerHTML = '';
        //then clone the wanted options from  available_sizes
        $.each(available_sizes, function (index, size_option) {
            $(size_select).append($(size_option).clone());
        });
        //change the default option to avoid duplicating
        size_select.selectedIndex = idx++;
        //add attr to mark the option as selected (needed to remove it from the array when selected option change)
        $('option:selected', size_select).attr('data-selected', 'true');
        //add selected option to selected_sizes array
        selected_sizes.push($('option:selected', size_select).attr('value'));
        //change the name of the color&size selects
        changeNames($(size_select).attr('data-num'));
    });
});

/*
 ** Function to prevent duplicated sizes
 */

$(document).on('change', '.prod-size', function () {
    let old_selected_size = $(this).find('option[data-selected="true"]');
    let new_selected_size = $('option:selected', this);
    //check if the new size is exist or not
    if ($.inArray(new_selected_size.attr('value'), selected_sizes) == -1) {
        //remove the old size from the array
        selected_sizes.splice(
            $.inArray(old_selected_size.attr('value'), selected_sizes),
            1
        );
        //remove its attribute data-selected
        old_selected_size.removeAttr('data-selected');
        //add the new size to the array
        selected_sizes.push(new_selected_size.attr('value'));
        //add data-selected attribute
        new_selected_size.attr('data-selected', 'true');
    } else {
        //else,the new size existed, show message "The size already selected"
        //change this (the select element) value to old_selected_size
        $(this).val(old_selected_size.attr('value'));
    }
    //call changeNames function to make name attributes same as selected size of this new size
    changeNames($(this).attr('data-num'));
});

/*
 ** Function to add color to selected colors when option clicked
 ** And add span with this color
 */

//create basic span to clone from it
let span_to_clone = $(
    '<span style="background-color: black" data-num="-1" data-colorid="colorIDhere"><i class="fa fa-trash"></i></span>'
);
$(document).on('change', '.js-prod-color', function () {
    let index = $(this).attr('data-num');
    let selected_option = $('option:selected', this);
    if ($.inArray($(selected_option).attr('value'), selectColors[index]) < 0) {
        //add the color
        selectColors[index].push($(selected_option).attr('value'));
        //change select value to selectColors[index]
        $(this).val(selectColors[index]); //does not matter, this from the old code
        $(`#parent-${index} .select-hidden`).val(selectColors[index]); // this from the new code
        //clone the basic span
        let cloned_span = span_to_clone.clone();
        //update it's color to match selected color
        cloned_span.css(
            'background-color',
            $(selected_option).attr('data-colorValue')
        );
        //update it's data-num to match its select data-num (needed for color deletion)
        cloned_span.attr('data-num', '' + index);
        //update the data-colorID to match the selected colorID (needed for color deletion ,to find the colorID in selected_array)
        cloned_span.attr('data-colorid', $(selected_option).attr('value'));
        //add the span to its container (div with class prod-data) in this select parent
        $(`#parent-${index} .prod-data`).append(cloned_span);
    }
});

/*
 ** Function to delete product color when span clicked
 */

$(document).on('click', '.add-prod-row span', function (e) {
    //get array index from span data-num attr
    let del_idx = $(this).attr('data-num');
    //remove the color
    let val_idx = $.inArray(
        $(this).attr('data-colorid'),
        selectColors[del_idx]
    );
    if (val_idx != -1) {
        selectColors[del_idx].splice(val_idx, 1);
        //update select value to selectColors[index] after removing
        $(`select[data-num=" ${del_idx}"]`).val(selectColors[del_idx]); //does not matter, this from the old code
        $(`#parent-${del_idx} .select-hidden`).val(selectColors[del_idx]); //this from the new code
        //remove the span
        this.remove();
    }
});

/*
 ** Function to add new product size when add-new-size button clicked
 */
//create deleteSize button to add it to the new size
let del_btn_toClone = $('<span><i class="fa fa-trash"></i> حذف المقاس</span>');
del_btn_toClone = $(
    '<div id="js-del-size-btn" class="add-prod-btn-2"></div>'
).append(del_btn_toClone);
// Start Function
$('#js-add-new-size').click(async () => {
    if (sizes_count < MAX_SIZES) {
        selectColors[sizes_count] = new Array();
        //clone from the basic size div
        let to_clone = $('#parent-0').clone();
        //remove its spans if there any
        to_clone.find('span').remove();
        //change its id to refere to a new size
        to_clone.attr('id', `parent-${sizes_count}`);

        to_clone.insertAfter('.add-prod-colorsandsize:last');
        await $(`#parent-${sizes_count} .js-prod-color`).attr(
            'data-num',
            sizes_count
        );
        $(`#parent-${sizes_count} .prod-size`).attr('data-num', sizes_count);
        //add delete button
        $(`#parent-${sizes_count}`).append(
            del_btn_toClone.clone().attr('data-num', sizes_count)
        );
        //change the selected option to avoid duplicating
        //first remove data-selected attribute from the selected option
        $(
            `#parent-${sizes_count} .prod-size option[data-selected="true"]`
        ).removeAttr('data-selected');
        //then find an option with size not in th array selected_sizes and make this option selected
        let options = $(`#parent-${sizes_count} .prod-size`).find('option');
        $.each(options, function (index, option) {
            let selected_value = $(option).attr('value');
            if (!selected_sizes.includes(selected_value)) {
                //make the option selected
                $(option).attr('data-selected', 'true');
                //add the selected option value to the array selected_sizes
                selected_sizes.push(selected_value);
                //change selected option of the select
                $(`#parent-${sizes_count} .prod-size`).val(selected_value);
                //stop looping
                return false;
            }
        });
        //call changeNames function to make name attributes same as selected size of this new size
        changeNames(sizes_count);
    } else {
        //TODO: show message "You can't add more than MAX_SIZES sizes"
    }
    // Increment the number of added sizes
    sizes_count++;
});

/*
 ** Function to delete product size
 ** Delete its selected value from the array selected_sizes
 */
$(document).on('click', '#js-del-size-btn', function () {
    let parent_num = $(this).attr('data-num');
    let selected_value = $(`#parent-${parent_num}`)
        .find('option[data-selected="true"]')
        .attr('value');
    selected_sizes.splice($.inArray(selected_value, selected_sizes), 1);
    $(`#parent-${parent_num}`).remove();
    sizes_count--;
});

/*
 ** Function to change the name of color&size selects to match the ID of selected size
 ** It takes two parameters :
 ** 1) parent_num => the parent number of color&size selects
 ** It will be called when the size in this parent change and that happened when:
 ** 1- the selected category  change
 ** 2- new size added
 ** 3- the selected size of size select in the itself change
 */
function changeNames(parent_num) {
    let size_select = $(`#parent-${parent_num} .prod-size`);
    let color_select = $(`#parent-${parent_num} .select-hidden`);
    let name = $('option:selected', size_select).attr('value');
    size_select.attr('name', `prodSize[${name}]`);
    color_select.attr('name', `prodSize[${name}][]`);
}
/*
 ********** End add product section ***********
 */

/*
 **********************************************
 ********* Start Update product section **********
 **********************************************
 */

/*
 ** Functon setSizes to set selected_sizes array
 ** Functon setColors to set selected_sizes array
 ** It called for update product section
 ** It store the product sizes & colors
 */
function setSizesColors(sizes, colors) {
    selected_sizes = [];
    for (let i in sizes) {
        selected_sizes.push(parseInt(i));
    }
    sizes_count = selected_sizes.length;
    selectColors = [];
    let k = 0; // counter for sizes
    if (sizes_count > 0) {
        for (let i in colors) {
            selectColors[k] = new Array();
            let parentNumForThisSize = $(`select[name='prodSize[${i}]']`).data(
                'num'
            );
            console.log(i + ' ' + parentNumForThisSize);
            for (let j in colors[i]) {
                selectColors[k].push(colors[i][j][0]);
            }
            k++;
        }
    }
    console.log(selected_sizes);
    console.log(selectColors);
}
/********* Start add product section **********/

/*
 **********************************************
 ****** Start Filtering product section *******
 **********************************************
 */

/*
 ** Functon Get Products Data v1.0
 ** Functon to get products from DB using ajax REQUEST
 ** It called to get products with specific kind of data
 ** It takes one parameter prodKind that indate whech products to get
 ** Param 1) prodKind can take one of these values:
 ** 1- number (category ID)
 ** 2- string (newProds or trending)
 */
$(document).on('click', '.prodFilter', function (e) {
    // create XML request object
    let prodkind = $(this).data('kind');
    $.ajax({
        method: 'GET',
        url: 'fetchProduct.php',
        data: { do: 'getProducts', kind: `${prodkind}` },
        success: function (products) {
            $('.prod-container .prod-cards-container').html(products);
        },
    });
});

/****** End Filtering product section ******/

/*
 **********************************************
 ********** Strart Home Slider code ***********
 **********************************************
 */
const nextbtn = document.querySelector('.btn-carousel.right');
const prevbtn = document.querySelector('.btn-carousel.left');
const slidercarousel = document.querySelector('.space-carousel');
let imgIndex = 0;
// Toggle Navgation
// Slider
nextbtn.addEventListener('click', () => {
    if (imgIndex < 5) {
        imgIndex++;
    }
    carouselImage();
});
prevbtn.addEventListener('click', () => {
    // remove classs active
    if (imgIndex > 0) {
        imgIndex--;
    }
    carouselImage();
});
function carouselImage() {
    slidercarousel.style.transform = `translateX(${imgIndex * -100}%)`;
}
setInterval(() => {
    if (imgIndex < 5) {
        imgIndex++;
    } else {
        imgIndex = 0;
    }
    carouselImage();
}, 3000);

/********** End Home Slider code************/
