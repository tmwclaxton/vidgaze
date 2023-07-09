import { defineStore } from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";

export const useCommentSectionStore = defineStore('CommentSectionStore', {
    state: () => {
        return {
            // showingStudioLinks: false,
            commentInteractions: [],

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

        getComments(item_id, item_type) {
            // $per_page = $request->input('per_page') ?? 10;
            // $comment_ids = $request->comment_ids ?? [];
            // $category = $request->input('category') ?? 'Order By';
            // $item_id = $request->input('item_id') ?? null;
            // $item_type = $request->input('item_type') ?? null;
            // $first_comment_id = $request->input('first_comment_id') ?? null;
            // $parent_comment_id = $request->input('parent_comment_id') ?? null;
            // query comments using ziggy to route name comments.infinite

        }

    }
});
