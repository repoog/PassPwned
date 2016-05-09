function weakpass(password)
{
	/*
	 * Check password strength in new password strength rules.
	 */
    var badrule1 = new RegExp('^[0-9]{0,14}$');
	var badrule2 = new RegExp('^[a-zA-Z]{0,8}$');
	var badrule3 = new RegExp('^[^a-zA-Z0-9]{0,9}$');
	var badrule4 = new RegExp('^[a-zA-Z0-9]{0,8}$');
	var badrule5 = new RegExp('^[^a-zA-Z]{0,9}$');
	var badrule6 = new RegExp('^.{0,7}$');
	var normalrule1 = new RegExp('^[0-9]{15,17}$');
	var normalrule2 = new RegExp('^[a-zA-Z]{9,10}$');
	var normalrule3 = new RegExp('^[^a-zA-Z0-9]{10,11}$');
	var normalrule4 = new RegExp('^[a-zA-Z0-9]{9}$');
	var normalrule5 = new RegExp('^[^a-zA-Z]{10}$');
	var normalrule6 = new RegExp('^.{8}$');
	
	var badrulelist = [badrule1, badrule2, badrule3, badrule4, badrule5, badrule6];
	var normalrulelist = [normalrule1, normalrule2, normalrule3, normalrule4, normalrule5, normalrule6];
	
	/**
	 * Return strength in three class with 0,1,2
	 * which stand for bad strength, normal strength and good strength.
	 */
	for (var key in badrulelist) {
		if (badrulelist[key].exec(password)) {
			return 0;
		}
	}
	
	for (var key in normalrulelist){
		if (normalrulelist[key].exec(password)) {
			return 1;
		}
	}
	
	return 2;
}