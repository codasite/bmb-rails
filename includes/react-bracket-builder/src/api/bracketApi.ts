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
		return await this.performRequest(this.bracketPath, 'GET');
	}

	async getBracket(id: number): Promise<BracketResponse> {
		return await this.performRequest(`${this.bracketPath}/${id}`, 'GET');
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

		const response = await fetch(`${this.baseUrl}${path}`, request);
		return response.json();
	}
}

export const bracketApi = new BracketApi();