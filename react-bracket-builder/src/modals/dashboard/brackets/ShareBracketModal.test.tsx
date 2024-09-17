import { render, screen } from '@testing-library/react'
import { ShareBracketModal } from './ShareBracketModal'
import userEvent from '@testing-library/user-event'

describe('ShareBracketModal', () => {
  test('renders modal correctly', async () => {
    const [show, setShow] = [true, jest.fn()]
    const [bracketData, setBracketData] = [
      {
        id: 1,
        title: 'Test bracket',
        month: 'January',
        year: '2022',
        fee: 100,
        playBracketUrl: 'testurl',
        copyBracketUrl: 'copyurl',
        mostPopularPicksUrl: 'popularurl',
      },
      jest.fn(),
    ]
    const { asFragment } = render(
      <ShareBracketModal
        show={show}
        setShow={setShow}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
    )
    expect(asFragment()).toMatchSnapshot()
  })
})
