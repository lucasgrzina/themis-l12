export default {
  methods: {
	addError (field, msg,rule,scope) {
		this.errors.add({
			field: field,
			msg: msg,
			rule: rule,
			scope: scope,
		})
	}
  }
}
