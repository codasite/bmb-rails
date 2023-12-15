import { render, screen } from '@testing-library/react'
import ShareBracketModal from './ShareBracketModal'
import userEvent from '@testing-library/user-event'

describe('ShareBracketModal', () => {
  test('renders modal correctly', async () => {
    const { asFragment } = render(
      <>
        <button
          className={'wpbb-share-bracket-button'}
          data-bracket-title={'Test bracket'}
          data-play-bracket-url={'testurl'}
        >
          Share Bracket
        </button>
        <ShareBracketModal />
      </>
    )
    await userEvent.click(screen.getByText('Share Bracket'))
    expect(asFragment()).toMatchSnapshot()
  })
})
