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
				label: 'Continue',
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
			this.panel1.$element.append('<p>This dialog uses an action set configured with modes. This is edit mode. Click \'help\' to see help mode. </p>');
			this.panel2 = new OO.ui.PanelLayout({padded: true, expanded: false});
			this.panel2.$element.append('<p>This is help mode. Only the \'back\' button is configured to be visible here. Click \'back\' to return to \'edit\' mode</p>');
			this.stackLayout = new OO.ui.StackLayout({
				items: [this.panel1, this.panel2]
			});
			this.$body.append(this.stackLayout.$element);
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
});
