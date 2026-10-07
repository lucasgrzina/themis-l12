<template>
    <div class="notification">
        <transition name="fade" mode="in-out">

            <div class="alert alert-success" v-if="success">
                <a href="javascript:void(0)" @click="hideSuccessNotification()" class="close" aria-label="close">&times;</a>
                <div class="container">{{ success }}</div>
            </div>
            <div class="alert alert-danger" v-if="error">
                <a href="javascript:void(0)" @click="hideErrorNotification()" class="close" aria-label="close">&times;</a>
                <div class="container" v-html="error"></div>
            </div>
            <div class="alert alert-info" v-if="info" >
                <a href="javascript:void(0)" @click="hideInfoNotification()" class="close" aria-label="close">&times;</a>
                <div class="container">{{ info }}</div>
            </div>
        </transition>
    </div>
</template>

<script>

    import {mapState, mapActions} from 'vuex';
    import { alert } from 'vue-strap'
    
    export default {
        name: 'notification',
        components: {
            alert
        },
        computed: {
            ...mapState({
                success: state => state.notification.success,
                error: state => state.notification.error,
                info: state => state.notification.info
            })
        },
        methods: {
            ...mapActions([
                'hideSuccessNotification',
                'hideErrorNotification',
                'hideInfoNotification'
            ])
        }
    }
</script>
