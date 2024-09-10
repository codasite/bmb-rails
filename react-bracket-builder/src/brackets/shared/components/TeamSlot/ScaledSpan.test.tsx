import '@testing-library/jest-dom/jest-globals'
import { act, render, screen } from '@testing-library/react'
import { ScaledSpan } from './ScaledSpan'

test('renders and scales the text correctly', () => {
  jest.useFakeTimers()
  render(
    <ScaledSpan targetWidth={50} style={{ color: 'red' }}>
      Scalable Text
    </ScaledSpan>
  )
  act(() => {
    jest.runAllTimers()
  })

  const spanElement = screen.getByText('Scalable Text')

  // Check if the element is rendered
  expect(spanElement).toBeInTheDocument()

  // Check if the scale was applied (from mock)
  expect(spanElement).toHaveStyle('transform: scale(0.5)') // Based on targetWidth and mock size
  expect(spanElement).toHaveStyle('visibility: visible') // Ensure the text is visible after scaling
})
