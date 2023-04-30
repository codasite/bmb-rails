import { Nullable } from "../types";

export interface BracketResponse {
	id: number;
	name: string;
	active: boolean;
	rounds: RoundResponse[];
}

export interface RoundResponse {
	id: number;
	name: string;
	depth: number;
	matches: MatchResponse[];
}

export interface MatchResponse {
	id: number;
	index: number;
	team1: Nullable<TeamResponse>;
	team2: Nullable<TeamResponse>;
	result: Nullable<TeamResponse>;
}

export interface TeamResponse {
	id: number;
	name: string;
	seed: Nullable<number>;
}

class BracketApi {
	private baseUrl: string;
	private bracketPath: string = 'brackets';

	constructor() {
		// @ts-ignore
		this.baseUrl = wpbb_ajax_obj.rest_url;
	}

	async getBrackets(): Promise<BracketResponse[]> {
		const res = await this.performRequest(this.bracketPath, 'GET');
		if (res.status !== 200) {
			throw new Error('Failed to get brackets');
		}
		return await res.json();
	}

	async getBracket(id: number): Promise<BracketResponse> {
		const res = await this.performRequest(`${this.bracketPath}/${id}`, 'GET');
		if (res.status !== 200) {
			throw new Error('Failed to get bracket');
		}
		return await res.json();
	}

	async deleteBracket(id: number): Promise<void> {
		const res = await this.performRequest(`${this.bracketPath}/${id}`, 'DELETE');
		if (res.status !== 204) {
			throw new Error('Failed to delete bracket');
		}
	}

	async setActive(id: number, active: boolean): Promise<boolean> {
		const path = `${this.bracketPath}/${id}/${active ? 'activate' : 'deactivate'}`
		const res = await this.performRequest(path, 'POST');
		if (res.status !== 200) {
			throw new Error('Failed to set active');
		}
		const activated = await res.json();
		return activated;
	}



	async performRequest(path: string, method: string, body: any = {}) {
		const request = {
			method,
			headers: {
				'Content-Type': 'application/json'
			},
		}
		if (method !== 'GET') {
			request['body'] = JSON.stringify(body);
		}

		return await fetch(`${this.baseUrl}${path}`, request);
	}
}

export const bracketApi = new BracketApi();