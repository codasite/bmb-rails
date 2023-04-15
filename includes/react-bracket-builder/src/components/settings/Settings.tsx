import React from 'react';
import Button from 'react-bootstrap/Button';

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
	return (
		<div>
			<h2 className='mt-4'>Bracket Builder Settings</h2>
			<Button variant='primary' className='mt-6'>Save</Button>
		</div>
	);
}

const fetchSports = () => {
	// @ts-ignore
	const sports = wpbb_ajax_obj.sports
	console.log(sports)
}


class BracketBuilderApi {
	url: string;
	static _instance: BracketBuilderApi;
	constructor() {
		// @ts-ignore
		this.url = wpbb_ajax_obj.rest_url;
	}
	static getInstance() {
		if (!BracketBuilderApi._instance) {
			// @ts-ignore
			BracketBuilderApi._instance = new BracketBuilderApi();
		}
		return BracketBuilderApi._instance;
	}
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

class SportsApi extends BracketBuilderApi {
	path: string = 'sports';
	async getSports() {
		return await this.performRequest(this.path, 'GET', {});
	}
}
// SportsApi.getInstance().getSports().then((sports) => {
// 	console.log(sports)
// })


export default Settings; 