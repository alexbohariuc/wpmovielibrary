
(function( $ ) {

	var editor = wpmoly.editor;

	editor.view = Backbone.View.extend({});

	editor.view.Search = Backbone.View.extend({

		el: '#wpmoly-movie-meta-search',

		model: editor.search,

		initialize: function() {

			this.template = _.template( $( '#wpmoly-movie-meta-search' ).html() );
			this.render();
		},

		render: function(  ) {
			this.$el.html( this.template() );
			return this;
		},

		events: {
			"click #wpmoly-search": "search",
			"click #wpmoly-update": "update",
			"click #wpmoly-empty": "empty",
			"change #wpmoly-search-query": "set"
		},

		set: function( event ) {

			this.model.set( { query: event.currentTarget.value } );
		},

		search: function( event ) {
			event.preventDefault();
			editor.movie.sync( 'search', this.model, {} );
		},

		update: function( event ) {
			event.preventDefault();
			console.log( 'update', event.currentTarget );
		},

		empty: function( event ) {
			event.preventDefault();
			console.log( 'empty', event.currentTarget );
		}
	});

	editor.view.Movie = Backbone.View.extend({

		el: '#wpmoly-movie-meta',

		model: editor.movie,

		events: {
			"change .meta-data-field": "update"
		},

		initialize: function() {

			this.template = _.template( $( '#wpmoly-movie-meta' ).html() );
			this.render();

			_.bindAll( this, 'render' );

			this.model.on( 'change', this.changed, this );
		},

		render: function( model ) {

			this.$el.html( this.template() );
			return this;
		},

		changed: function( model ) {

			_.each( model.changed, function( meta, key ) {
				$( '#meta_data_' + key ).val( meta );
			} );
		},

		update: function( event ) {

			var meta = event.currentTarget.id.replace('meta_data_',''),
			   value = event.currentTarget.value;

			this.model.set( meta, value );
		}
	});

	editor.view.Results = Backbone.View.extend({

		el: '#wpmoly-meta-search-results',

		collection: editor.results,

		events : {
			'click .wpmoly-select-movie a' : 'get'
		},

		initialize: function() {

			this.template = _.template( $( '#wpmoly-search-results-template' ).html() );

			_.bindAll( this, 'render' );
			this.collection.bind( 'change', this.render );
			this.collection.bind( 'add', this.render );
		},

		render: function() {

			var results = this.template( { results : this.collection.toJSON() } );

			$( this.el ).show();
			$( this.el ).html( results );

			return this;
		},

		get: function( event ) {

			event.preventDefault();

			var id = event.currentTarget.hash.replace('#','');

			editor.search.set( 'type', 'id' );
			editor.search.set( 'query', id );

			editor.movie.sync( 'get', this.model, {} );
		}

	});

	editor.view.Panel = Backbone.View.extend({

		el: '#wpmoly-metabox',

		model: editor.panel,

		initialize: function () {

			this.template = _.template( $( '#wpmoly-metabox' ).html() );
			this.render();

			if ( window.innerWidth < 1180 )
				this.resize();
		},

		render: function () {
			this.$el.html( this.template() );
			return this;
		},

		events: {
			"click #wpmoly-meta-menu a": "navigate"
		},

		navigate: function( event ) {

			event.preventDefault();

			var $panel = $( event.currentTarget.hash ),
			      $tab = $( event.currentTarget.parentElement ),
			   $panels = $( '.panel' ),
			     $tabs = $( '.tab' );

			if ( undefined == $panel || undefined == $tab || $tab.hasClass( 'off' ) )
				return false;

			$panels.removeClass( 'active' );
			$tabs.removeClass( 'active' );
			$panel.addClass( 'active' );
			$tab.addClass( 'active' );
		},

		/**
		 * Resize Metabox Panel
		 *
		 * @since 2.0
		 */
		resize: function() {
			$( this.el ).toggleClass( 'small' );
		}
	});

	new editor.view.Panel();

	new editor.view.Movie();
	new editor.view.Search();
	new editor.view.Results();

})(jQuery);
