<template>
  <!-- Modal -->
    <div
      class="modal fade"
      id="modalLoadPdf"
      data-backdrop="static"
      data-keyboard="false"
      tabindex="-1"
      aria-labelledby="modalLoadPdfLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body py-5">
            <div class="w-100">
              <moon-loader
                class="m-auto"
                :loading="loading"
                :color="'#032F6C'"
                :size="100"
              />
            </div>
            <h3 class="mt-3 text-center">Generando...</h3>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  props: ["loading"],
  data() {
    return {};
  },
  watch: {
    loading() {
      if (this.loading) {
        $("#modalLoadPdf").modal("show");
      } else {
        $("#modalLoadPdf").modal("hide");
      }
    },
  },
};
</script>

<style>
</style>