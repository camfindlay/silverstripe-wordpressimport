(function($) {

	$.entwine('ss', function($) {

		$(".wpimport-action").entwine({

			/**
			 * Onclick initiates the evaluation
			**/
			onclick: function() {
				$(this).addClass("loading");
				if($(this).hasClass("evaluate")) {
					this.evaluate();
				} else if ($(this).hasClass("import")) {
					this.import();
				}
			},



			/**
			 * Get the upload field config
			**/
			getConfig: function() {
				return this.getUploadField().getConfig();
			},



			/**
			 * Get the current file id
			**/
			getFileId: function() {
				return $(this).parents(".ss-uploadfield-item").attr("data-fileid");
			},



			/**
			 * Fires off an ajax request to evaluate the upload
			**/
			evaluate: function() {
				var config = this.getConfig();
				var url = config.urlEvaluate;

				var self = $(this);
				jQuery.ajax({
					url: url
						+ '?file=' 
						+ this.getFileId(),
					success: function(data) {
					 	for(key in data) {
							console.log(data[key].Count + " " + data[key].Title + " to create.");
						}
						self.removeClass("loading");
					}
				});
			},



			/**
			 * Fires off an ajax request to complete the import
			**/
			import: function() {
				var config = this.getConfig();
				var url = config.urlImport;

				var self = $(this);
				jQuery.ajax({
					url: url
						+ '?file=' 
						+ this.getFileId(),
					success: function(data) {
					 	for(key in data) {
							console.log(data[key].Count + " " + data[key].Title + " created.");
						}
						self.removeClass("loading");
					}
				});
			}

		});

	});

}(jQuery));