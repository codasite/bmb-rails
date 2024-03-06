import { render } from '@testing-library/react'
import { NumTeamsPage } from './NumTeamsPage'
import {
  teamPickerDefaults,
  teamPickerMax,
  teamPickerMin,
} from './BracketBuilder'
import { WildcardPlacement } from '../../shared/models/WildcardPlacement'
import { fourTeamMatchTree } from '../../shared/models/MatchTreeFakeFactory'

describe('NumTeamsPage', () => {
  test('renders page correctly', () => {
    const noop = () => {}
    const { asFragment } = render(
      <NumTeamsPage
        numTeams={16}
        setNumTeams={noop}
        onAddTeamsClick={noop}
        setTeamPickerState={noop}
        teamPickerDefaults={teamPickerDefaults}
        teamPickerMin={teamPickerMin}
        teamPickerMax={teamPickerMax}
        wildcardPlacement={WildcardPlacement.Center}
        teamPickerState={teamPickerDefaults.map((val, i) => ({
          currentValue: val,
          selected: i === 0,
        }))}
        setWildcardPlacement={noop}
        matchTree={fourTeamMatchTree()}
        bracketMeta={{ title: 'Test bracket', date: 'test date' }}
      />
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
