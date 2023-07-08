import { defineStore } from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";

const toastStore = useToastStore();
export const useCommentSectionStore = defineStore('CommentSectionStore', {
    state: () => {
        return {
            // showingStudioLinks: false,
            commentInteractions: [],

        }
    },
    actions: {


        storeComment(body, item_id, item_type, parent_comment_id = null) {
            console.log("storeComment")
            if (body.length === 0) {
                toastStore.add({
                    message: "Comment not long enough",
                    type: "error"
                });
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
    }
});
