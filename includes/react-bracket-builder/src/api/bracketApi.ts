import { BracketReq, BracketRes, SubmissionRes, SubmissionReq } from './types/bracket';



class BracketApi {
	private baseUrl: string;
	private bracketPath: string = 'brackets';
	private submissionPath: string = 'bracket-picks';
	private nonce: string = '';

	constructor() {
		// @ts-ignore
		this.baseUrl = wpbb_ajax_obj.rest_url;
		// @ts-ignore
		this.nonce = wpbb_ajax_obj.nonce;
	}

	async getBrackets(): Promise<BracketRes[]> {
		const res = await this.performRequest(this.bracketPath, 'GET');
		if (res.status !== 200) {
			throw new Error('Failed to get brackets');
		}
		return camelCaseKeys(await res.json());
	}

	async getBracket(id: number): Promise<BracketRes> {
		const res = await this.performRequest(`${this.bracketPath}/${id}`, 'GET');
		if (res.status !== 200) {
			throw new Error('Failed to get bracket');
		}
		return camelCaseKeys(await res.json());
	}

	async getSubmissions(id?: number | null): Promise<SubmissionRes[]> {
		const params = id ? { bracketId: id } : {};
		const res = await this.performRequest(`${this.submissionPath}`, 'GET', params);
		if (res.status !== 200) {
			throw new Error('Failed to get submissions');
		}
		return camelCaseKeys(await res.json());
	}

	async getSubmission(id: number): Promise<SubmissionRes> {
		const res = await this.performRequest(`${this.submissionPath}/${id}`, 'GET');
		if (res.status !== 200) {
			throw new Error('Failed to get submission');
		}
		return camelCaseKeys(await res.json());
	}

	async createSubmission(submission: SubmissionReq): Promise<SubmissionRes> {
		const res = await this.performRequest(this.submissionPath, 'POST', submission);
		if (res.status !== 201) {
			throw new Error('Failed to create submission');
		}
		return camelCaseKeys(await res.json());
	}

	async createBracket(bracket: BracketReq): Promise<BracketRes> {
		const res = await this.performRequest(this.bracketPath, 'POST', bracket);
		if (res.status !== 201) {
			throw new Error('Failed to create bracket');
		}
		return camelCaseKeys(await res.json());
	}

	async deleteBracket(id: number): Promise<void> {
		const res = await this.performRequest(`${this.bracketPath}/${id}`, 'DELETE');
		console.log(res)
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
		const snakeBody = snakeCaseKeys(body);
		const request = {
			method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': this.nonce,
			},
		}
		if (method !== 'GET') {
			request['body'] = JSON.stringify(snakeBody);
		} else if (Object.keys(body).length > 0) {
			// pass params as query string
			path += '?' + Object.entries(snakeBody).map(([key, value]) => `${key}=${value}`).join('&');
		}
		console.log(path)
		console.log(request)

		return await fetch(`${this.baseUrl}${path}`, request);
	}

}

// Utility function to convert snake_case to camelCase
function toCamelCase(str: string): string {
	return str.replace(/([-_][a-z])/g, (group) =>
		group.toUpperCase().replace('-', '').replace('_', '')
	);
}

// Recursive function to convert object keys to camelCase
function camelCaseKeys(obj: any): any {
	if (Array.isArray(obj)) {
		return obj.map((value) => camelCaseKeys(value));
	} else if (typeof obj === 'object' && obj !== null) {
		return Object.entries(obj).reduce((accumulator: any, [key, value]) => {
			accumulator[toCamelCase(key)] = camelCaseKeys(value);
			return accumulator;
		}, {});
	}
	return obj;
}

function camelCaseToSnakeCase(str: string): string {
	return str.replace(/[A-Z]/g, (match) => `_${match.toLowerCase()}`);
}

// Recursive function to convert object keys to snake_case
function snakeCaseKeys(obj: any): any {
	if (Array.isArray(obj)) {
		return obj.map((value) => snakeCaseKeys(value));
	} else if (typeof obj === 'object' && obj !== null) {
		return Object.entries(obj).reduce((accumulator: any, [key, value]) => {
			accumulator[camelCaseToSnakeCase(key)] = snakeCaseKeys(value);
			return accumulator;
		}, {});
	}
	return obj;
}

export const bracketApi = new BracketApi();