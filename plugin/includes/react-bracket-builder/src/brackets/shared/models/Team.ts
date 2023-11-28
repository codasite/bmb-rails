import { TeamRepr, TeamReq } from '../api/types/bracket'

export class Team {
  name: string
  id: number | null
  constructor(name: string, id: number | null = null) {
    this.name = name
    this.id = id
  }
  equals(team: Team): boolean {
    if (team.id && this.id) {
      return this.name === team.name && this.id === team.id
    }
    return this.name === team.name
  }
  clone(): Team {
    return new Team(this.name, this.id)
  }
  serialize(): TeamRepr {
    return {
      name: this.name,
      id: this.id ? this.id : undefined,
    }
  }
  toTeamReq(): TeamReq {
    return {
      name: this.name,
    }
  }
}
