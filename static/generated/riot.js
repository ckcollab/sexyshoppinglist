riot.tag2('competition-list', '<h1>List of comps yo</h1> <ul> <li each="{competitions}"> {title} </li> </ul>', '', '', function(opts) {
        var self = this

        self.on('mount', function() {
            $.get('http://localhost:8000/api/competitions/')
                .done(function(competitions){
                    self.update({competitions: competitions})
                })
        })
});
riot.tag2('search', '<div class="ui stackable grid container"> <div class="row centered"> <div class="twelve wide column"> <div class="ui form"> <div class="inline fields"> <div class="field"> <div id="time-range" class="ui floating labeled icon dropdown button"> <i class="filter icon"></i> <span class="text">Any time</span> <div class="menu"> <div class="header"> Timeframe </div> <div class="divider"></div> <div class="item" data-value="active"> Active </div> <div class="item" data-value="past_month"> Started past month </div> <div class="item" data-value="past_year"> Started past year </div> <div class="divider"></div> <div class="header"> Date range </div> <div class="ui left icon input datepicker"> <i class="calendar icon"></i> <input type="text" name="search" placeholder="Start date"> </div> <div class="ui left icon input datepicker"> <i class="calendar icon"></i> <input type="text" name="search" placeholder="End date"> </div> </div> </div> </div> <div class="field"> <div class="ui floating labeled icon dropdown multiple button"> <i class="filter icon"></i> <span class="text">Attributes (select many)</span> <div class="menu"> <div class="header"> <i class="tags icon"></i> Competition filters </div> <div class="item"> I\'m in </div> <div class="item"> Has not finished </div> </div> </div> </div> <div class="field"> <div class="ui floating labeled icon dropdown button"> <i class="filter icon"></i> <span class="text">Sorted by</span> <div class="menu"> <div class="item"> Next deadline </div> <div class="item"> Prize amount </div> <div class="item"> Number of participants </div> </div> </div> </div> </div> </div> <div id="search_wrapper" ref="search_wrapper" class="ui fluid search focus"> <div class="ui icon input fluid"> <input ref="search_field" class="prompt" type="text" placeholder="Keywords""> <i class="search icon"></i> </div> </div> </div> </div> <div id="results_container" class="row centered"> <div class="twelve wide column"> <div class="ui divided stacked items"> <search-result each="{results}"></search-result> </div> </div> </div> </div>', 'search #results_container,[data-is="search"] #results_container{ min-height: 375px; } search #search_wrapper .results,[data-is="search"] #search_wrapper .results{ margin-top: 1px; } search .ui.button:hover .icon,[data-is="search"] .ui.button:hover .icon{ opacity: 1; }', '', function(opts) {
        var self = this

        self.on('mount', function () {

            $('.datepicker').calendar({
                type: 'date',
                popupOptions: {
                    position: 'bottom left',
                    lastResort: 'bottom left',
                    hideOnScroll: false
                }
            })
            $(".ui.dropdown").dropdown()

            $(self.refs.search_wrapper).search({
                apiSettings: {
                    url: URLS.API + "query/?q={query}",
                    onResponse: function (data) {

                        self.update({
                            results: data.results,
                            suggestions: data.suggestions
                        })

                        var response = {
                            results: []
                        };
                        $.each(data.suggestions, function (index, item) {
                            response.results.push({
                                title: item.text

                            });
                        });
                        return response;
                    }
                },
                cache: false,
                showNoResults: false,
                minCharacters: 2,
                duration: 300,
                transition: 'slide down'
            });
        })

        self.input_updated = function () {
            delay(function () {
                self.search()
            }, 100)
        }

});

riot.tag2('search-result', '<div class="image"> <img src="https://semantic-ui.com/images/wireframe/image.png"> </div> <div class="content"> <a class="header">{title}</a> <div class="meta"> <span class="price">$1200</span> <span class="stay">1 Month</span> </div> <div class="description"> <p>Blah blah lorem ipsum dolor sit amet, description about a competition.</p> </div> <div class="extra"> <div class="ui right floated primary button"> Participate <i class="right chevron icon"></i> </div> </div> </div>', '', 'class="item"', function(opts) {
});
