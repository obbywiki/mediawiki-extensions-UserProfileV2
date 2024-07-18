/**
 * The actual code to show the popup window to edit a profile
 * All of this was copied from https://www.mediawiki.org/wiki/OOUI/Windows/Process_Dialogs
 * in the interim until it is adapted
 *
 */
$(document).ready(function () {
	(function () {

		function ProcessDialog(config) {
			ProcessDialog.super.call(this, config);
		}

		OO.inheritClass(ProcessDialog, OO.ui.ProcessDialog);
		ProcessDialog.static.name = 'userProfileV2Edit';
		ProcessDialog.static.title = 'Edit Profile';

		ProcessDialog.static.actions = [
			{
				action: 'continue',
				modes: 'edit',
				label: 'Save',
				flags: ['primary', 'progressive']
			},
			{
				action: 'help',
				modes: 'edit',
				label: 'Help'
			},
			{
				modes: 'edit',
				label: 'Cancel',
				flags: ['safe', 'close']
			},
			{
				action: 'back',
				modes: 'help',
				label: 'Back',
				flags: ['safe', 'back']
			}
		];

		ProcessDialog.prototype.initialize = function () {

			ProcessDialog.super.prototype.initialize.apply(this, arguments);

			this.panel1 = new OO.ui.PanelLayout({padded: true, expanded: false});
			this.addFieldsToPanel(this.panel1);
			this.panel2 = new OO.ui.PanelLayout({padded: true, expanded: false});
			this.panel2.$element.append('<p>Use this dialog to change the contents of your user profile.</p>');
			this.stackLayout = new OO.ui.StackLayout({
				items: [this.panel1, this.panel2]
			});
			this.$body.append(this.stackLayout.$element);

			// get our data and set it into the form
			const dialog = this;
			getUserData().then(function (userData) {
				dialog.aboutMe.setValue(userData.query[0]['profile-aboutme'] || '');
				dialog.discordLink.setValue(userData.query[0]['profile-discord'] || '');
				dialog.twitterLink.setValue(userData.query[0]['profile-twitter'] || '');
				dialog.showGlobalGroups.setSelected(userData.query[0]['profile-show-globalgroups'] === "1");
				dialog.showGlobalEditCount.setSelected(userData.query[0]['profile-show-globaledits'] === "1");
			}).catch(function (error) {
				console.error('Could not set the data for this user');
			});
		};

		// Set up the initial mode of the window ('edit', in this example.)
		ProcessDialog.prototype.getSetupProcess = function (data) {
			return ProcessDialog.super.prototype.getSetupProcess.call(this, data)
				.next(function () {
					this.actions.setMode('edit');
				}, this);
		};

		// Use the getActionProcess() method to set the modes and displayed item.
		ProcessDialog.prototype.getActionProcess = function (action) {

			if (action === 'help') {
				// Set the mode to help.
				this.actions.setMode('help');
				// Show the help panel.
				this.stackLayout.setItem(this.panel2);
			} else if (action === 'back') {
				// Set the mode to edit.
				this.actions.setMode('edit');
				// Show the edit panel.
				this.stackLayout.setItem(this.panel1);
			} else if (action === 'continue') {
				var dialog = this;
				return new OO.ui.Process(function () {
					// Do something about the edit.
					dialog.close();
				});
			}
			return ProcessDialog.super.prototype.getActionProcess.call(this, action);
		};


		ProcessDialog.prototype.getBodyHeight = function () {
			return this.panel1.$element.outerHeight(true);
		};

		ProcessDialog.prototype.addFieldsToPanel = function (panel) {
			var fieldset = this.getEditFields();
			panel.$element.append(fieldset.$element);
		};

		ProcessDialog.prototype.getEditFields = function () {
			this.aboutMe = new OO.ui.MultilineTextInputWidget({
				placeholder: 'About Me'
			});
			this.discordLink = new OO.ui.TextInputWidget({
				placeholder: 'joebloggs'
			});
			this.twitterLink = new OO.ui.TextInputWidget({
				placeholder: '@johnappleseed'
			});
			this.showGlobalGroups = new OO.ui.CheckboxInputWidget({
				selected: false
			});
			this.showGlobalEditCount = new OO.ui.CheckboxInputWidget({
				selected: false
			});

			var fieldset = new OO.ui.FieldsetLayout({
				label: 'Edit Your Profile',
				classes: ['container']
			});

			fieldset.addItems([
				new OO.ui.FieldLayout(this.aboutMe, {
					label: 'About Me',
					align: 'top',
					help: 'Please keep it short (200 characters).',
					helpInline: true
				}),
				new OO.ui.FieldLayout(this.discordLink, {
					label: 'Discord Username',
					align: 'top',
					help: 'Your Discord Username (must not contain #)'
				}),
				new OO.ui.FieldLayout(this.twitterLink, {
					label: 'Twitter Username',
					align: 'top',
					help: 'Your Twitter Username, with the @'
				}),
				new OO.ui.FieldLayout(this.showGlobalGroups, {
					label: 'Show my global usergroups?',
					align: 'inline',
					help: "Show global user groups that I am part of (will be visible to everyone)"
				}),
				new OO.ui.FieldLayout(this.showGlobalEditCount, {
					label: 'Show my global editcount?',
					align: 'inline',
					help: "Show my global edit count from CentralAuth (will be visible to everyone)"
				})
			]);

			return fieldset;
		};


		var windowManager = new OO.ui.WindowManager();
		$(document.body).append(windowManager.$element);


		var processDialog = new ProcessDialog({
			size: 'medium'
		});

		windowManager.addWindows([processDialog]);

		$('#userProfileV2-edit').on('click', function () {
			windowManager.openWindow(processDialog);
		});

	})();

	function getUserData() {
		const api = new mw.Api();
		return api.get({
			action: 'query',
			format: 'json',
			list: 'queryuserprofilev2',
			user_name: mw.config.get('wgRelevantUserName')
		}).then(function (userData) {
			console.log(userData);
			return userData;
		}).catch(function (error) {
			console.error('API request failed:', error);
			throw error;
		});
	}
});
