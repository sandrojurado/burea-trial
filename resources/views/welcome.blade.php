<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MarkdownEditor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.2/css/bulma.min.css">
    <link rel="stylesheet" href="{{url('/css/app.css')}}">
</head>
<body>
<div id="app">
    <div id="content" class="section is-paddingless full-height">
        <div class="container full-height">
            <div class="columns full-height">
                <div class="column is-2 is-paddingless cell full-height block-gray">
                    <div class="header block-dark padding-10">
                        <p class="text-white bold">
                            MarkdownEditor
                            <a class="is-pulled-right " id="add-document" v-on:click="addDocument">
                                <i class="fas fa-plus text-white"></i>
                            </a>
                        </p>
                    </div>
                    <div class="full-height">
                        <a class="button is-bordered is-pulled-right scroll-up is-paddingless" v-on:click="scrollUp">
                            <i class="fas fa-chevron-up fs-6"></i>
                        </a>
                        <div  class="doc-list">
                            <div class="cell doc-item columns" v-for="document in documents" :class="((selected !== null && selected.id === document.id)? 'selected' : '') ">
                                <div class="column is-2" v-on:click="selectDocument(document.id)">
                                    <i class="fas fa-file text-white fs-14"></i>
                                </div>
                                <div class="column is-8" v-on:click="selectDocument(document.id)">
                                    <span class="document-title">@{{ getTitle(document) }}</span><br/>
                                    <span class="document-date">@{{ formatDate(document.updated_at) }}</span>
                                </div>
                                <div class="column is-2" v-on:click="deleteDocument(document.id)">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        </div>
                        <a class="button is-bordered scroll-down" v-on:click="scrollDown">
                            <i class="fas fa-chevron-down fs-6"></i>
                        </a>
                    </div>
                </div>
                <div id="editor" class="column is-5 is-paddingless is-clipped cell">
                    <textarea class="full-height full-width padding-10"  v-model="content" v-on:keyup="updateDocument(selected.id, content)">@{{ (selected !== null && selected.id !== deleted)? selected.content : '' }}</textarea>
                </div>
                <div class="column is-5 block-light padding-10">
                    <vue-markdown v-bind:source="content"></vue-markdown>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="{{url('/plugins/vue-markdown/vue-markdown.js')}}"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="{{url('/plugins/moment/moment.js')}}"></script>
<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
<script>
    Vue.use(VueMarkdown);
    var app = new Vue({
        el: '#app',
        data: {
            opened: false,
            hoveredRow: null,
            documents: [],
            selected: null,
            deleted: null,
            goals: [],
            isEnabled: true,
            date: null,
            content: '',
        },
        methods: {
            getDocuments () {
                axios.get('/api/document')
                    .then(function (response) {
                        this.documents = response.data.documents;
                    }.bind(this))
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            selectDocument (id) {
                axios.get('/api/document/'+id)
                    .then(function (response) {
                        this.selected = response.data.document;
                        this.content = (response.data.document.content !== null)? response.data.document.content : '';
                        document.querySelector('textarea').focus();
                    }.bind(this))
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            getTitle (item) {
                let body = (item.title !== '') ? item.title : 'Untitled';
                return body.length > 15 ? body.substring(0, 15) + '...' : body;
            },
            formatDate (date) {
                return moment(date).startOf('hour').fromNow();
            },
            scrollDown() {
                let content = document.querySelector(".doc-list");
                let scrollAmount = 0;
                let slide = content.offsetHeight * 0.7;
                let slideTimer = setInterval(function(){
                    content.scrollTop += 10;
                    scrollAmount += 10;
                    if(scrollAmount >= slide){
                        window.clearInterval(slideTimer);
                    }
                }, 15);
            },
            scrollUp() {
                let content = document.querySelector(".doc-list");
                let scrollAmount = 0;
                let slide = content.offsetHeight * 0.7;
                let slideTimer = setInterval(function(){
                    content.scrollTop -= 10;
                    scrollAmount += 10;
                    if(scrollAmount >= slide){
                        window.clearInterval(slideTimer);
                    }
                }, 15);
            },
            updateDocument(id, content) {
                if (this.selected !== null && id !== this.deleted) {
                    axios.put('/api/document/'+id,{
                        content: content
                    }).then(function (response) {
                        this.documents = response.data.documents;
                    }.bind(this))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            deleteDocument(item) {
                if (confirm('Do you want to delete this item?')) {
                    if (this.selected !== null && item === this.selected.id) {
                        this.content = '';
                    }
                    axios.delete('/api/document/'+item).then(function (response) {
                        this.documents = response.data.documents;
                    }.bind(this))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            addDocument() {
                if (confirm('Create new document?')) {
                    axios.post('/api/document/')
                        .then(function (response) {
                            this.documents = response.data.documents;
                            this.selected = response.data.selected;
                            this.content = (response.data.selected.content !== null)? response.data.selected.content : '';
                            document.querySelector('textarea').focus();
                        }.bind(this))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            addBullet(goal, item, evaluation) {
                if (evaluation === 0) {
                    axios.post('/api/class/bullet',{
                        item: item,
                        goal: goal
                    }).then(function (response) {
                        this.classes = response.data.classes;
                    }.bind(this))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            inArray(needle, haystack) {
                var length = haystack.length;
                for(var i = 0; i < length; i++) {
                    if(haystack[i] == needle) return true;
                }
                return false;
            }

        },
        mounted() {
            this.getDocuments();
        },
    });
</script>
</body>
</html>
