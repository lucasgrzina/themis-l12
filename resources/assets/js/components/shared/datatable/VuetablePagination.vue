<template>
  <ul v-show="tablePagination && tablePagination.last_page > 1"  :class="css.wrapperClass">
    <li @click="loadPage(1)"
      :class="['btn-nav', css.linkClass, isOnFirstPage ? css.disabledClass : '']">
        <a class="page-link" href="javascript:void(0)">
          <i v-if="css.icons.first != ''" :class="[css.icons.first]"></i>
          <span v-else>&laquo;</span>
        </a>
    </li>    
    <li @click="loadPage('prev')"
      :class="['btn-nav', css.linkClass, isOnFirstPage ? css.disabledClass : '']">
        <a class="page-link" href="javascript:void(0)">
          <i v-if="css.icons.next != ''" :class="[css.icons.prev]"></i>
          <span v-else>&nbsp;&lsaquo;</span>
        </a>
    </li>
  
    <template v-if="notEnoughPages">
        <li @click="loadPage(n)"
          :class="[css.pageClass, isCurrentPage(n) ? css.activeClass : '']"
          v-for="n in totalPage"
          >
          <a class="page-link" href="javascript:void(0)">{{ n }}</a>
        </li>
    </template>
    <template v-else>
      <template v-for="n in windowSize">
        <li @click="loadPage(windowStart+n-1)"
          :class="[css.pageClass, isCurrentPage(windowStart+n-1) ? css.activeClass : '']">
          <a class="page-link" href="javascript:void(0)">{{ windowStart+n-1 }}</a>
        </li>
      </template>
    </template>
    <li @click="loadPage('next')"
      :class="['btn-nav', css.linkClass, isOnLastPage ? css.disabledClass : '']">
      <a class="page-link" href="javascript:void(0)">
        <i v-if="css.icons.next != ''" :class="[css.icons.next]"></i>
        <span v-else>&rsaquo;&nbsp;</span>
      </a>
    </li>
    <li @click="loadPage(totalPage)"
      :class="['btn-nav', css.linkClass, isOnLastPage ? css.disabledClass : '']">
      <a class="page-link" href="javascript:void(0)">
        <i v-if="css.icons.last != ''" :class="[css.icons.last]"></i>
        <span v-else>&raquo;</span>
      </a>
    </li>
  </ul>
</template>

<script>
import PaginationMixin from 'vuetable-2/src/components/VuetablePaginationMixin.vue'

export default {
  mixins: [PaginationMixin],
}
</script>
