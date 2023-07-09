import { defineStore } from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";

export const useCommentSectionStore = defineStore('CommentSectionStore', {
    state: () => {
        return {
            // showingStudioLinks: false,
            commentInteractions: [],
            item: null,
            item_type: null,

        }
    },
    actions: {


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
            })
            .catch(error => {
                // Handle comment creation error
                toastStore.add({
                    message: 'Something went wrong, please try again later',
                    type: 'error'
                });
            });


        },

        getCommentInteractions(item_id, item_type) {
            axios.get(route('comments.index', {
                item_id: item_id,
                item_type: item_type,
            }))
            .then(response => {
                this.commentInteractions = response.data;
            })
            .catch(error => {
                console.log(error);
            });
        },


        shareComment(comment) {
            const shareStore = useShareModalStore();

            shareStore.showMenu = true; // show share menu
            const link = route('watch.show', { video: {slug: this.item.slug }, comment: comment.id });
            const title = "Check out this comment on VidGaze" + this.item.title
            shareStore.getShareLinks(link, title);
        }

    }
});
