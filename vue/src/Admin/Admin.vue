<!--
  InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform

  @link https://www.innocraft.com
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="translate('JsTrackerCustom_JsTrackerCustom')"
    feature="true"
  >
    <h3>{{ translate('JsTrackerCustom_AddCustomJs') }}</h3>

    <form method="post" action="">
      <textarea name="customJs" :value="customJs"></textarea>
      <input type="hidden" name="customJsNonce" :value="customJsNonce">
      <button type="submit" class="btn">{{ translate('General_Save') }}</button>
    </form>
  </ContentBlock>

</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';

interface AdminState {
  customJs: string;
}

export default defineComponent({
  props: {
    initialCustomJs: String,
    customJsNonce: {
      type: String,
      required: true,
    },
  },
  data(): AdminState {
    return {
      customJs: this.initialCustomJs || '',
    };
  },
  components: {
    ContentBlock,
  },
});
</script>
