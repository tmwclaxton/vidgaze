<script setup>
import PaddingLayout from '@/Layouts/Partials/ConsistentPadding.vue';
import {useAuthStore} from "@/Stores/AuthStore";
import {Link} from "@inertiajs/inertia-vue3";
import {useToastStore} from "@/Stores/ToastStore";
import {onMounted, ref} from "vue";
const toastStore = useToastStore();
const authStore = useAuthStore();

const slug = ref('');
const role = ref('moderator');


//
// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::get('list_moderators', [AdministratorController::class, 'listModerators'])->name('list_moderators');
// });
//
// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::get('list_mod_actions', [AdministratorController::class, 'listModActions'])->name('paginate_mod_actions');
// });
// list moderators
// public function listModerators(Request $request): \Illuminate\Http\JsonResponse
// {
//     $page = $request->query('page') ?? 1;
//     try {
//         $moderators = User::where('role', 'moderator')->paginate(10, ['*'], 'page', $page);
//         return response()->json($moderators);
//     } catch (\Exception $e) {
//     Log::error($e->getMessage());
//     return response()->json([
//         'errors' => ['general' => [Responses::MODERATOR_LIST_FAILED]]
// ], 500);
// }
// }
//
// // list mod actions
// public function listModActions(Request $request): \Illuminate\Http\JsonResponse
// {
//     $page = $request->query('page') ?? 1;
//     try {
//         $modActions = ModeratorAction::paginate(10, ['*'], 'page', $page);
//         return response()->json($modActions);
//     } catch (\Exception $e) {
//     Log::error($e->getMessage());
//     return response()->json([
//         'errors' => ['general' => [Responses::MODERATOR_LIST_ACTIONS_FAILED]]
// ], 500);
// }
// }
// public function up(): void
// {
//     Schema::create('moderator_actions', function (Blueprint $table) {
//     $table->id();
//     $table->foreignId('user_id')->constrained()->onDelete('set null');
//     $table->string('human_readable_action');
//     $table->timestamps();
// });
// }

const changeUserRole = () => {
    axios.post(route('api.admin.change_user_role'), {creator_slug: slug.value, role: role.value}).then(response => {
        toastStore.add({
            message: 'User role changed successfully.',
            type: 'success',
        });
    }).catch(error => {
        toastStore.add({
            message: 'Sorry we couldn\'t change the user role!  Please try again.',
            type: 'warning',
        });
    });
}

const moderators = ref([]);
const modActions = ref([]);

const fetchModerators = (page = 1) => {
    currentModPage.value = page;
    axios.get(route('api.admin.list_moderators', {page: page})).then(response => {
        moderators.value = response.data.data;
    }).catch(error => {
        toastStore.add({
            message: 'Sorry we couldn\'t fetch the moderators!  Please try again.',
            type: 'warning',
        });
    });
}

// const fetchModActions = (page = 1) => {
//     axios.get(route('api.admin.list_mod_actions', {page: page})).then(response => {
//         modActions.value = response.data.data;
//     }).catch(error => {
//         toastStore.add({
//             message: 'Sorry we couldn\'t fetch the moderator actions!  Please try again.',
//             type: 'warning',
//         });
//     });
// }

const currentModPage = ref(1);
const currentModActionPage = ref(1);

onMounted(() => {
    fetchModerators();
    // fetchModActions();
});


</script>
<template>

    <PaddingLayout>
        <SeoHead title="Admin" description="VidGaze administration tools." noindex />
        <div class="h1 text dark:textDark select-all">


            <div class="  px-2 sm:rounded-lg w-full flex flex-col align-middle justify-center justify-items-center">

                <!--Welcome message + name-->
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-200 mb-5 text-center ">Welcome back, {{ authStore.user.creator.name }}!</h1>

                <!--Horizon and Telescope links-->
                <div class=" mx-auto max-w-md lg:max-w-screen-xl mb-5 ">

                    <!-- Change User Role, input field for user slug and select field for role -->
                    <div class=" p-4 rounded-lg w-full flex flex-col gap-2 shadow-xl">
                            <div class="uppercase text-sm text-zinc-400">
                                Change User Role
                            </div>
                            <div class="flex space-x-2 items-center">
                                <input v-model="slug"
                                    type="text" class="w-40 p-2 border border-zinc-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="User Slug">
                                <select v-model="role"
                                    class="w-40 p-2 border border-zinc-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option value="admin">Admin</option>
                                    <option value="moderator">Moderator</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button @click="changeUserRole('creator-slug', 'role')"
                                    class="w-40 p-2 bg-blue-500 text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-blue-600 dark:focus:ring-blue-600 dark:focus:border-blue-600">
                                Change Role
                            </button>
                    </div>

                    <!--List moderators table with pagination-->
                    <div class=" p-4 rounded-lg w-full flex flex-col gap-2 shadow-xl">
                        <div class="uppercase text-sm text-zinc-400">
                            List Moderators
                        </div>
                        <table class="w-full">
                            <thead>
                            <tr>
                                <th class="text-left">Name</th>
                                <th class="text-left">Slug</th>
                                <th class="text-left">Link</th>
                                <th class="text-left">Role</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="moderator in moderators" :key="moderator.id">
                                <td>{{ moderator.creator.name }}</td>
                                <td>{{ moderator.creator.slug }}</td>
                                <td class="text-blue-500">
                                    <Link :href="route('channel.show', {slug: moderator.creator.slug})">Go to channel</Link>
                                </td>
                                <td>{{ moderator.role }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="flex justify-center gap-2">
                            <button @click="fetchModerators(currentModPage - 1)"
                                    v-if="currentModPage > 1"
                                    class="p-2 bg-blue-500 text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-blue-600 dark:focus:ring-blue-600 dark:focus:border-blue-600">Previous</button>
                            <p>{{ currentModPage }}</p>
                            <button @click="fetchModerators(currentModPage + 1)" class="p-2 bg-blue-500 text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-blue-600 dark:focus:ring-blue-600 dark:focus:border-blue-600">Next</button>
                        </div>

                    </div>




                </div>


            </div>
        </div>
    </PaddingLayout>

</template>
