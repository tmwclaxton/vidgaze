<template>


    <!-- Main modal -->
    <div class="fixed left-0 bottom-0 z-50 w-max h-max p-4 overflow-x-hidden overflow-y-auto  ">
        <div class="relative w-full max-w-2xl  ">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-zinc-800 shadow-md border-zinc-200 border dark:border-none">
                <!-- Modal header -->
                <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-zinc-600">
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">
                        Cookie Consent Settings
                    </h3>
                    <button type="button"
                            class="text-zinc-400 bg-transparent hover:bg-zinc-200 hover:text-zinc-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-zinc-600 dark:hover:text-white"
                            data-modal-hide="defaultModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-6  ">
                    <p class="text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                        To use VidGaze, cookies are needed for functionality.  By clicking on “Accept all”, you declare your
                        consent to the use of all Services.
                        <br>
                        <br>
                        I can't guarentee that the site will work without cookies, but you're welcome to try!
                    </p>

                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-6 pt-0 space-x-2  border-zinc-200 rounded-b dark:border-zinc-600">
                    <button data-modal-hide="defaultModal" type="button"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Accept All
                    </button>
                    <button data-modal-hide="defaultModal" type="button"
                            class="text-zinc-500 bg-white hover:bg-zinc-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-zinc-200 text-sm font-medium px-5 py-2.5 hover:text-zinc-900 focus:z-10 dark:bg-zinc-700 dark:text-zinc-300 dark:border-zinc-500 dark:hover:text-white dark:hover:bg-zinc-600 dark:focus:ring-zinc-600">
                        Reject All
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>

</script>

