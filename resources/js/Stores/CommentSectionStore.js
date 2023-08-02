import { defineStore } from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";

export const useCommentSectionStore = defineStore('CommentSectionStore', {
    state: () => {
        return {
            // showingStudioLinks: false,
            commentInteractions: [],
            comments: [],
            item: null,
            item_type: null,

        }
    },
    getters: {
        // comment count iwht pluralization
        commentCount() {
            return this.comments.length + " Comment" + (this.comments.length != 1 ? 's' : '');
        }
    },
    actions: {
        async fetchComments(category, parent_comment_id = null, first_comment_id = null,loadMore = false) {
            const toastStore = useToastStore();
            try {
                let comment_ids = null;
                if (loadMore) {
                    comment_ids = this.comments.map(comment => comment.id).join(',');
                } else {
                    this.comments = [];
                }

                const response = await axios.get(route('api.comment.index'), {
                    params: {
                        item_id: this.item.id,
                        item_type: this.item.type,
                        per_page: 10,
                        comment_ids: comment_ids,
                        category: category,
                        // grab comment attribute from url parameter
                        first_comment_id: first_comment_id,
                        parent_comment_id: parent_comment_id,
                    }
                }).then(
                    response => {
                        return response;
                    }
                ).catch(error => {
                        console.log(error);
                    }
                )

                setTimeout(() => {
                    // console.log(response.data);
                    if (!response.data.error) {
                        this.comments = this.comments.concat(response.data.comments);

                        // if load more and no comment toast message
                        if (loadMore === true && response.data.comments.length === 0) {

                            // toastStore.add({
                            //     message: "No more comments",
                            //     type: "warning"
                            // });
                        }

                    } else {
                        console.log(response.data.error);
                    }
                }, 200); // 200ms delay
            } catch (error) {
                console.log(error);
            }
        },

        async getCommentInteractions() {
            if (!useAuthStore().user) {
                return;
            }
            axios.get(route('comment.interactions', {
                item_id: this.item.id,
                item_type: this.item_type,
            }))
                .then(response => {
                    // console.log(response.data);
                    this.commentInteractions = response.data.result;
                })
                .catch(error => {
                    console.log(error);
                });
        },

        getCommentInteraction(comment_id) {
            //0 both unselected, 1 like button, 2-->
            // check liked value if like then 1, if dislike then 2 otherwise null
            // console.log(comment_id);

            const commentInteraction = this.commentInteractions.find(interaction => interaction.comment_id === comment_id);
            // console.log(commentInteraction);
            if (commentInteraction) {
                if (commentInteraction.liked === 'like') {
                    return 1;
                } else if (commentInteraction.liked === 'dislike') {
                    return 2;
                }
            }
            return 0;

        },

        storeComment(body, parent_comment_id = null) {
            const toastStore = useToastStore();

            if (body.length === 0) {
                toastStore.add({
                    message: "Comment not long enough",
                    type: "error"
                });
                return;
            }

            // make request using ziggy to route name comments.store
            axios.post(route('comments.store', {
                item_id: this.item.id,
                item_type: this.item.type,
                parent_comment_id: parent_comment_id,
                body: body
            }))
            .then(response => {
                // Handle successful comment creation
                toastStore.add({
                    message: response.data.message,
                    type: response.data.type
                });

                // add comment to comments array
                this.comments.unshift(response.data.comment);

                // if parent id is not null then add 1 to reply count of parent comment
                if (parent_comment_id) {
                    const parentComment = this.comments.find(comment => comment.id === parent_comment_id);
                    parentComment.reply_count++;
                }

            })
            .catch(error => {
                // Handle comment creation error
                toastStore.add({
                    message: 'Something went wrong, please try again later',
                    type: 'warning'
                });
            });


        },

        shareComment(comment) {
            const shareStore = useShareModalStore();

            shareStore.showMenu = true; // show share menu
            const link = route('watch.show', { video: {slug: this.item.slug }, comment: comment.id });
            const title = "Check out this comment on VidGaze" + this.item.title
            shareStore.getShareLinks(link, title);
        },

        deleteComment(comment_id) {
            const toastStore = useToastStore();

            axios.delete(route('comments.destroy', {
                comment_id: comment_id,
                item_id: this.item.id,
                item_type: this.item_type,
            }))
                .then(response => {
                    // console.log(response.data);
                    toastStore.add({
                        message: response.data.message,
                        type: response.data.type
                    });

                    // remove comment from comments array
                    this.comments = this.comments.filter(comment => comment.id !== comment_id);

                    // if parent id is not null then subtract 1 from reply count of parent comment
                    if (response.data.parent_comment_id) {
                        const parentComment = this.comments.find(comment => comment.id === response.data.parent_comment_id);
                        parentComment.reply_count--;
                    }

                })
                .catch(error => {
                    console.log(error);
                    toastStore.add({
                        message: 'Something went wrong, please try again later',
                        type: 'warning'
                    });
                });
        },

        editComment(comment_id, body) {
            const toastStore = useToastStore();

            axios.put(route('comments.update', {
                comment_id: comment_id,
                item_id: this.item.id,
                item_type: this.item_type,
                body: body
            }))
                .then(response => {
                    // console.log(response.data);
                    toastStore.add({
                        message: response.data.message,
                        type: response.data.type
                    });

                    // update comment in comments array
                    const commentIndex = this.comments.findIndex(comment => comment.id === comment_id);
                    this.comments[commentIndex].body = body;

                })
                .catch(error => {
                    console.log(error);
                    toastStore.add({
                        message: 'Something went wrong, please try again later',
                        type: 'warning'
                    });
                });
        }

    }
});
