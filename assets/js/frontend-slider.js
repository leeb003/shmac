/* Script to call in for displaying the sliders on calculators that have it set */
jQuery(function ($) {  // use $ for jQuery
    "use strict";
    $(document).ready(function(){
		//var calc_inc = SHMAC_Slider.calc_inc;
		var slider_vars = SHMAC_Slider.slider_vars;
		console.log(slider_vars);
		$.each( slider_vars, function( k, v) {
			var calc_inc            = v.calc_inc;
			var current_slider      = v.calc_inc;
			var defaultpurchase     = slider_vars[calc_inc].defaultpurchase;
			var sliderminamount     = slider_vars[calc_inc].sliderminamount;
			var sliderstepsamount   = slider_vars[calc_inc].sliderstepsamount;
			var slidermaxamount     = slider_vars[calc_inc].slidermaxamount;
			var currencyformat      = slider_vars[calc_inc].currencyformat;
			var slidermininterest   = slider_vars[calc_inc].slidermininterest;
			var sliderstepsinterest = slider_vars[calc_inc].sliderstepsinterest;
			var slidermaxinterest   = slider_vars[calc_inc].slidermaxinterest;
			var defaultinterest     = slider_vars[calc_inc].defaultinterest;
			var downpayshow         = slider_vars[calc_inc].downpayshow;
			var slidermindown       = slider_vars[calc_inc].slidermindown;
			var sliderstepsdown     = slider_vars[calc_inc].sliderstepsdown;
			var slidermaxdown       = slider_vars[calc_inc].slidermaxdown;
			var defaultdown         = slider_vars[calc_inc].defaultdown;
			var downpaytype         = slider_vars[calc_inc].downpaytype;
			var sliderminterm       = slider_vars[calc_inc].sliderminterm;
			var sliderstepsterm     = slider_vars[calc_inc].sliderstepsterm;
			var slidermaxterm       = slider_vars[calc_inc].slidermaxterm;
			var defaultterm         = slider_vars[calc_inc].defaultterm;

			//Amount Slider- Input Script
			var amtStart= defaultpurchase;
			amtStart = parseFloat(amtStart.replace(/,/g , ''));
			var amtMin= sliderminamount;
			amtMin = parseFloat(amtMin.replace(/,/g , ''));
			var amtStep = parseFloat(sliderstepsamount);
			var amtMax = slidermaxamount;
			amtMax = parseFloat(amtMax.replace(/,/g , ''));
			window ['amount_slider_' + calc_inc] = document.getElementById("amount_slider_" + calc_inc);
			var amount_slider_value = $(".amountinput_" + calc_inc);

			var mark = ',';
			var thousand = '.';

			if (currencyformat == 2) {
				mark = ',';
				thousand = '.';
			} else if(currencyformat == 3) {
				mark = ',';
				thousand = ' ';
			} else if (currencyformat == 1) {
				mark = '.';
				thousand = ',';
			}

			noUiSlider.create(eval('amount_slider_' + calc_inc), {
				start: amtStart,
				step: amtStep,
				range: {
					'min': amtMin,
					'max': amtMax
				},
				format:wNumb({
					decimals: 2,                            
					mark: mark,
					thousand: thousand,
            	})
			});
			eval('amount_slider_' + calc_inc).noUiSlider.on('update', function( values, handle ){
				if ( handle == 0 ) {
					amount_slider_value.val(values[handle]);
					amount_slider_value.autoNumeric('init');
				}
			});
			amount_slider_value.on('change', function(){
				amount_slider_value.autoNumeric('init');               
				['amount_slider_' + calc_inc].noUiSlider.set(amount_slider_value.val());
				amount_slider_value.autoNumeric('init');    
			});
			//Interest Slider- Input Script
			var intMin= parseFloat(slidermininterest);
			var intStep = parseFloat(sliderstepsinterest);
			var intMax = parseFloat(slidermaxinterest);
			var intStart = parseFloat(defaultinterest);
			window ['interest_slider_' + calc_inc] = document.getElementById("interest_slider_" + calc_inc);
			var interest_slider_value = $(".interestinput_" + calc_inc);

			noUiSlider.create(eval('interest_slider_' + calc_inc), {
				start: intStart,
				step: intStep,
				range: {
					'min': intMin,
					'max': intMax
				},format:wNumb({
					decimals: 2,                            
					mark: mark,
				})
			});             
			eval('interest_slider_' + calc_inc).noUiSlider.on('update', function( values, handle ){
				if ( handle == 0 ) {
					interest_slider_value.val(values[handle]);
					interest_slider_value.autoNumeric('init');   
				}
			});                 
			//DownPayment Slider- Input Script
			if (downpayshow=="yes") { 
				var dwnpayMin= parseFloat(slidermindown);
				var dwnpayStep = parseFloat(sliderstepsdown);
				var dwnpayMax = parseFloat(slidermaxdown);
				var dwnpayStart = parseFloat(defaultdown);
				window ['downpay_slider_' + calc_inc] = document.getElementById("downpay_slider_" + calc_inc);
				var downpay_slider_value = $(".downpayinput_" + calc_inc);

				if (downpaytype == 'amount') {
					mark = mark;
					thousand = thousand;
				} else {
					mark = mark;
					thousand = '';
				}
			
				noUiSlider.create(eval('downpay_slider_' + calc_inc), {
					start: dwnpayStart,
					step: dwnpayStep,
					range: {
						'min': dwnpayMin,
						'max': dwnpayMax
					},
					format: wNumb({
						decimals: 2,                            
						mark: mark,
						thousand: thousand,
					})
                        
				});             
				eval('downpay_slider_' + calc_inc).noUiSlider.on('update', function( values, handle ){
					if(handle==0){
						downpay_slider_value.val(values[handle]);
						downpay_slider_value.autoNumeric('init');
					}
				});
                    
				downpay_slider_value.on('change', function(){
					downpay_slider_value.autoNumeric('init');
					var downpayVal = this.value ;                  
					['downpay_slider_' + calc_inc].noUiSlider.set(downpayVal);
				});
			}  // end if downpayshow is yes

			//Term Slider- Input Script
			var termMin= parseFloat(sliderminterm);
			var termStep = parseFloat(sliderstepsterm);
			var termMax = parseFloat(slidermaxterm);
			var termStart = parseFloat(defaultterm);
			window ['term_slider_' + calc_inc] = document.getElementById("term_slider_" + calc_inc);
			var term_slider_value = $(".terminput_" + calc_inc);                   
			noUiSlider.create(eval('term_slider_' + calc_inc), {
				start: termStart,
				step: termStep,
				range: {
					'min': termMin,
					'max': termMax
				},
				format: wNumb({decimals: 0})
			});             
			eval('term_slider_' + calc_inc).noUiSlider.on('update', function( values, handle ){
				var value = values[handle];
				if(handle==0){
					term_slider_value.val(value);
				}
			});             
			term_slider_value.on('change', function(){
				var termVal = Number(this.value.replace(/,/g , ''));                        
				['term_slider_' + calc_inc].noUiSlider.set(termVal);
			});
			$(".shmac-reset_" + calc_inc).on("click",function(){
				eval('amount_slider_' + calc_inc).noUiSlider.reset();
				eval('term_slider_' + calc_inc).noUiSlider.reset();
				eval('downpay_slider_' + calc_inc).noUiSlider.reset();
				eval('interest_slider_' + calc_inc).noUiSlider.reset();                     
			});
		});
	});
});
