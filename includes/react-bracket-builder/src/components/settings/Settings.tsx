import React, { useState } from 'react';
import Button from 'react-bootstrap/Button';
import { BracketModal, Bracket } from '../bracket_builder/Bracket';

class Sport {
	id: number;
	name: string;
	teams: Team[];
	constructor(id: number, name: string, teams: Team[]) {
		this.id = id;
		this.name = name;
		this.teams = teams;
	}
}

class Team {
	id: number;
	name: string;
	constructor(id: number, name: string) {
		this.id = id;
		this.name = name;
	}
}



const Settings = () => {
	const [showBracketModal, setShowBracketModal] = useState(false)

	const handleCloseBracketModal = () => setShowBracketModal(false);
	const handleSaveBracketModal = () => setShowBracketModal(false);
	const handleShowBracketModal = () => setShowBracketModal(true);
	return (
		<div>
			<h3 className='mt-4'>Bracket Builder Settings</h3>

			<Button variant='primary' className='mt-6' onClick={handleShowBracketModal}>Save</Button>
			<BracketModal show={showBracketModal} handleCancel={handleCloseBracketModal} handleSave={handleSaveBracketModal} />
			<Bracket />
		</div>
	);
}


class BracketBuilderApi {
	url: string;
	// static _sportsApi: SportsApi;
	// static _bracketApi: BracketApi;
	constructor() {
		// @ts-ignore
		this.url = wpbb_ajax_obj.rest_url;
	}
	// static getBracketApi() {
	// 	if (!BracketBuilderApi._bracketApi) {
	// 		// @ts-ignore
	// 		BracketBuilderApi._bracketApi = new BracketApi();
	// 	}
	// 	return BracketBuilderApi._bracketApi;
	// }

	// static getSportsApi() {
	// 	if (!BracketBuilderApi._sportsApi) {
	// 		// @ts-ignore
	// 		BracketBuilderApi._sportsApi = new SportsApi();
	// 	}
	// 	return BracketBuilderApi._sportsApi;
	// }
	async performRequest(path: string, method: string, body: any) {
		const response = await fetch(`${this.url}${path}`, {
			method,
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(body)
		});
		return response.json();
	}
}

class BracketApi extends BracketBuilderApi {
	path: string = 'brackets';
	async getBrackets() {
		return await this.performRequest(this.path, 'GET', {});
	}
}

// class SportsApi extends BracketBuilderApi {
// 	path: string = 'sports';
// 	async getSports() {
// 		return await this.performRequest(this.path, 'GET', {});
// 	}
// }
// SportsApi.getInstance().getSports().then((sports) => {
// 	console.log(sports)
// })
const fetchBrackets = async () => {
	// @ts-ignore
	const res = await fetch(`${wpbb_ajax_obj.rest_url}brackets`);
	const brackets = await res.json();
	console.log(brackets)
}
fetchBrackets();

export default Settings; 