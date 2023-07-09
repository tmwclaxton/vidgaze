import { defineStore } from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";

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
    actions: {
        async fetchComments(item_id, item_type, category) {
            try {
                const comment_ids = this.comments.map(comment => comment.id).join(',');
                const response = await axios.get(route('comments.infinite'), {
                    params: {
                        item_id: item_id,
                        item_type: item_type,
                        per_page: 10,
                        comment_ids: comment_ids,
                        category: category,
                        first_comment_id: null,
                        parent_comment_id: null
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
                    } else {
                        console.log(response.data.error);
                    }
                }, 200); // 200ms delay
            } catch (error) {
                console.log(error);
            }
        },

        async getCommentInteractions() {
            axios.get(route('comment.interactions', {
                item_id: this.item.id,
                item_type: this.item_type,
            }))
                .then(response => {
                    // console.log(response.data);
                    this.commentInteractions = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        },

        getCommentInteraction(comment_id) {
            //0 both unselected, 1 like button, 2-->
            // check liked value if like then 1, if dislike then 2 otherwise null
            // console.log(comment_id);

            const commentInteraction = this.commentInteractions.result.find(interaction => interaction.comment_id === comment_id);
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

        storeComment(body, item_id, item_type, parent_comment_id = null) {
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
                item_id: item_id,
                item_type: item_type,
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

            })
            .catch(error => {
                // Handle comment creation error
                toastStore.add({
                    message: 'Something went wrong, please try again later',
                    type: 'error'
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

                })
                .catch(error => {
                    console.log(error);
                    toastStore.add({
                        message: 'Something went wrong, please try again later',
                        type: 'error'
                    });
                });
        }

    }
});
