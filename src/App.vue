<template>
	<!--
    SPDX-FileCopyrightText: Sebastian Scheibe <sebascheibe@gmail.com>
    SPDX-License-Identifier: AGPL-3.0-or-later
    -->
	<div id="content" class="app-wol">
		<AppNavigation>
			<AppNavigationNew v-if="!loading" :text="t('wol', 'New device')" :disabled="false" button-id="new-wol-button"
				button-class="icon-add" @click="newDevice" />
			<ul>
				<AppNavigationItem v-for="device in devices" :key="device.id"
					:title="device.title ? device.title : t('wol', 'New device')"
					:class="{ active: currentDeviceId === device.id }" @click="openDevice(device)">
					<template slot="actions">
						<ActionButton v-if="device.id === -1" icon="icon-close" @click="cancelNewDevice(device)">
							{{
								t('wol', 'Cancel device creation') }}
						</ActionButton>
						<ActionButton v-else icon="icon-delete" @click="deleteDevice(device)">
							{{
								t('wol', 'Delete device') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
		</AppNavigation>
		<AppContent>
			<div v-if="currentDevice">
				<input ref="title" v-model="currentDevice.title" type="text" :disabled="updating">
				<textarea ref="mac" v-model="currentDevice.mac" :disabled="updating" />
				<input type="button" class="primary" :value="t('wol', 'Save')" :disabled="updating || !savePossible"
					@click="saveDevice">
				<input type="button" class="primary" :value="t('wol', 'Wake up')" :disabled="updating || !savePossible"
					@click="wakeDevice">
			</div>
			<div v-else id="emptycontent">
				<div class="icon-file" />
				<h2>{{
					t('wol', 'Create a device to get started') }}</h2>
			</div>
		</AppContent>
	</div>
</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
	},
	data() {
		return {
			devices: [],
			currentDeviceId: null,
			updating: false,
			wakingUp: false,
			loading: true,
		}
	},
	computed: {
		/**
		 * Return the currently selected device object
		 * @returns {Object|null}
		 */
		currentDevice() {
			if (this.currentDeviceId === null) {
				return null
			}
			return this.devices.find((device) => device.id === this.currentDeviceId)
		},

		/**
		 * Returns true if a device is selected and its title is not empty
		 * @returns {Boolean}
		 */
		savePossible() {
			return this.currentDevice && this.currentDevice.title !== ''
		},
	},
	/**
	 * Fetch list of devices when the component is loaded
	 */
	async mounted() {
		try {
			const response = await axios.get(generateUrl('/apps/wol/devices'))
			this.devices = response.data
		} catch (e) {
			console.error(e)
			showError(t('devices', 'Could not fetch devices'))
		}
		this.loading = false
	},

	methods: {
		/**
		 * Create a new device and focus the device mac field automatically
		 * @param {Object} device Device object
		 */
		openDevice(device) {
			if (this.updating) {
				return
			}
			this.currentDeviceId = device.id
			this.$nextTick(() => {
				this.$refs.mac.focus()
			})
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new device or save
		 */
		saveDevice() {
			if (this.currentDeviceId === -1) {
				this.createDevice(this.currentDevice)
			} else {
				this.updateDevice(this.currentDevice)
			}
		},
		/**
		 * Action tiggered when clicking the wake up button
		 * TODO: disable button for timeout to avoid double clicking
		 */
		wakeDevice() {
			this.wakeUpDevice(this.currentDevice)

		},
		/**
		 * Create a new device and focus the device mac field automatically
		 * The device is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newDevice() {
			if (this.currentDeviceId !== -1) {
				this.currentDeviceId = -1
				this.devices.push({
					id: -1,
					title: '',
					mac: '',
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		/**
		 * Abort creating a new device
		 */
		cancelNewDevice() {
			this.devices.splice(this.devices.findIndex((device) => device.id === -1), 1)
			this.currentDeviceId = null
		},
		/**
		 * Create a new device by sending the information to the server
		 * @param {Object} device Device object
		 */
		async createDevice(device) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/wol/devices'), device)
				const index = this.devices.findIndex((match) => match.id === this.currentDeviceId)
				this.$set(this.devices, index, response.data)
				this.currentDeviceId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('devices', 'Could not create the device'))
			}
			this.updating = false
		},
		/**
		 * Update an existing device on the server
		 * @param {Object} device Device object
		 */
		async updateDevice(device) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/wol/devices/${device.id}`), device)
			} catch (e) {
				console.error(e)
				showError(t('devices', 'Could not update the device'))
			}
			this.updating = false
		},
		/**
		 * wakeUp device using its MAC address to send a magic packet to
		 * @param {Object} device Device object
		 */
		async wakeUpDevice(device) {
			this.wakingUp = true
			try {
				//TODO: send magic packet
				await axios.post(generateUrl(`/apps/wol/wake/${device.id}`), device)
			} catch (e) {
				console.error(e)
				showError(t('device', 'Could not wake up the device'))
			}
			this.updating = false
		},
		/**
	 * Delete a device, remove it from the frontend and show a hint
	 * @param {Object} device Device object
	 */
		async deleteDevice(device) {
			try {
				axios.delete(generateUrl(`/apps/wol/devices/${device.id}`))
				this.devices.splice(this.devices.indexOf(device), 1)
				if (this.currentDeviceId === device.id) {
					this.currentDeviceId = null
				}
				showSuccess(t('wol', 'Device deleted'))
			} catch (e) {
				console.error(e)
				showError(t('wol', 'Could not delete the device'))
			}
		},
	},
}
</script>
<style scoped>
#app-content>div {
	width: 100%;
	height: 100%;
	padding: 20px;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
}

input[type='text'] {
	width: 100%;
}

textarea {
	flex-grow: 1;
	width: 100%;
}
</style>
